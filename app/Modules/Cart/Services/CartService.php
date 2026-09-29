<?php

namespace App\Modules\Cart\Services;

use App\Core\Commerce\GuestDevice;
use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductVariant;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const MaxQuantity = 99;

    public function __construct(
        private readonly GuestDevice $guestDevice,
        private readonly WishlistService $wishlist,
    ) {}

    public function count(Request $request): int
    {
        return (int) $this->ownerQuery($request)->sum('quantity');
    }

    /**
     * @return list<int>
     */
    public function productIds(Request $request): array
    {
        return $this->ownerQuery($request)
            ->distinct()
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Free-delivery threshold and flat fee applied to smaller orders.
     *
     * @return array{freeAbove: float, fee: float}
     */
    public function deliveryRule(): array
    {
        return [
            'freeAbove' => (float) config('commerce.delivery.free_above'),
            'fee' => (float) config('commerce.delivery.fee'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(Request $request): Collection
    {
        return $this->ownerQuery($request)
            ->with(['product.images', 'product.category', 'product.subcategory', 'variant.size', 'variant.color'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (CartItem $item): array => $this->toArray($item))
            ->values();
    }

    public function add(Request $request, int $productId, int $quantity = 1, ?int $variantId = null): CartItem
    {
        $quantity = max(1, $quantity);
        $product = Product::query()->where('is_unlaunched', false)->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages(['product_id' => 'Product not found.']);
        }

        $variant = $this->resolveVariant($product, $variantId);
        $variantKey = CartItem::variantKey($variant?->id);
        $owner = $this->guestDevice->owner($request);

        $item = $this->findOwnerItem($owner, $product->id, $variantKey);

        if ($item !== null) {
            $item->update(['quantity' => $item->quantity + $quantity]);

            return $item->fresh(['product', 'variant']);
        }

        return CartItem::query()->create([
            'user_id' => $owner['user_id'],
            'guest_token' => $owner['guest_token'],
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'variant_key' => $variantKey,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(Request $request, int $itemId, int $quantity): void
    {
        $item = $this->findOwnedItemOrFail($request, $itemId);

        if ($quantity < 1) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(Request $request, int $itemId): void
    {
        $this->findOwnedItemOrFail($request, $itemId)->delete();
    }

    /**
     * Remove the given cart lines; ids that belong to someone else are ignored.
     *
     * @param  list<int>  $itemIds
     */
    public function removeMany(Request $request, array $itemIds): int
    {
        return $this->ownerQuery($request)->whereKey($itemIds)->delete();
    }

    /**
     * Save the given cart lines' products to the wishlist and drop them from the cart.
     *
     * @param  list<int>  $itemIds
     */
    public function moveToWishlist(Request $request, array $itemIds): int
    {
        $items = $this->ownerQuery($request)->whereKey($itemIds)->get();

        DB::transaction(function () use ($request, $items): void {
            foreach ($items->pluck('product_id')->unique() as $productId) {
                $this->wishlist->add($request, (int) $productId);
            }

            CartItem::query()->whereKey($items->modelKeys())->delete();
        });

        return $items->count();
    }

    public function mergeGuestToUser(string $guestToken, User $user): void
    {
        DB::transaction(function () use ($guestToken, $user): void {
            $guestItems = CartItem::query()->where('guest_token', $guestToken)->get();

            foreach ($guestItems as $guestItem) {
                $existing = CartItem::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('variant_key', $guestItem->variant_key)
                    ->first();

                if ($existing !== null) {
                    $existing->update([
                        'quantity' => $existing->quantity + $guestItem->quantity,
                    ]);
                    $guestItem->delete();

                    continue;
                }

                $guestItem->update([
                    'user_id' => $user->id,
                    'guest_token' => null,
                ]);
            }
        });
    }

    /**
     * @return Builder<CartItem>
     */
    private function ownerQuery(Request $request)
    {
        $owner = $this->guestDevice->owner($request);

        return CartItem::query()->when(
            $owner['user_id'] !== null,
            fn ($query) => $query->where('user_id', $owner['user_id']),
            fn ($query) => $query->where('guest_token', $owner['guest_token']),
        );
    }

    /**
     * @param  array{user_id: ?int, guest_token: ?string}  $owner
     */
    private function findOwnerItem(array $owner, int $productId, string $variantKey): ?CartItem
    {
        return CartItem::query()
            ->when(
                $owner['user_id'] !== null,
                fn ($query) => $query->where('user_id', $owner['user_id']),
                fn ($query) => $query->where('guest_token', $owner['guest_token']),
            )
            ->where('product_id', $productId)
            ->where('variant_key', $variantKey)
            ->first();
    }

    private function findOwnedItemOrFail(Request $request, int $itemId): CartItem
    {
        $item = $this->ownerQuery($request)->whereKey($itemId)->first();

        if ($item === null) {
            abort(404);
        }

        return $item;
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if ($variantId === null) {
            return null;
        }

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereKey($variantId)
            ->first();

        if ($variant === null) {
            throw ValidationException::withMessages(['product_variant_id' => 'Invalid product variant.']);
        }

        return $variant;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(CartItem $item): array
    {
        $product = $item->product;
        $variant = $item->variant;
        $unit = $variant?->effectivePrice() ?? $product?->effectivePrice() ?? 0.0;
        $mrp = max($unit, (float) ($variant?->price ?? $product?->price ?? 0));
        $line = $unit * $item->quantity;
        $stock = $variant !== null ? (int) $variant->stock : ($product?->availableStock() ?? 0);

        return [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'name' => $product?->name,
            'slug' => $product?->slug,
            'image' => $product?->mainImageUrl(),
            'category' => $product?->subcategory?->name ?? $product?->category?->name,
            'size' => $variant?->size?->name,
            'color' => $variant?->color?->name,
            'unit_price' => $unit,
            'unit_price_label' => '₹'.number_format($unit, 2),
            'unit_mrp' => $mrp,
            'line_total' => $line,
            'line_total_label' => '₹'.number_format($line, 2),
            'line_mrp' => $mrp * $item->quantity,
            'discount_percent' => $mrp > $unit ? (int) round((1 - $unit / $mrp) * 100) : 0,
            'max_quantity' => min(self::MaxQuantity, max($stock, $item->quantity)),
            'in_stock' => $stock > 0,
        ];
    }
}
