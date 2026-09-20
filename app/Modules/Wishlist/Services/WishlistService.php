<?php

namespace App\Modules\Wishlist\Services;

use App\Core\Commerce\GuestDevice;
use App\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Wishlist\Models\WishlistItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function __construct(private readonly GuestDevice $guestDevice) {}

    public function count(Request $request): int
    {
        return $this->ownerQuery($request)->count();
    }

    /**
     * @return list<int>
     */
    public function productIds(Request $request): array
    {
        return $this->ownerQuery($request)->pluck('product_id')->map(fn ($id): int => (int) $id)->values()->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(Request $request): Collection
    {
        return $this->ownerQuery($request)
            ->with(['product.images', 'product.category'])
            ->orderByDesc('id')
            ->get()
            ->map(function (WishlistItem $item): ?array {
                $product = $item->product;
                if ($product === null || $product->is_unlaunched) {
                    return null;
                }

                return [
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->toStorefrontArray()['price'],
                    'compareAtPrice' => $product->toStorefrontArray()['compareAtPrice'],
                    'image' => $product->mainImageUrl(),
                    'inStock' => $product->availableStock() > 0,
                ];
            })
            ->filter()
            ->values();
    }

    public function toggle(Request $request, int $productId): bool
    {
        $product = Product::query()->where('is_unlaunched', false)->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages(['product_id' => 'Product not found.']);
        }

        $owner = $this->guestDevice->owner($request);
        $existing = $this->findOwnerItem($owner, $product->id);

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        WishlistItem::query()->create([
            'user_id' => $owner['user_id'],
            'guest_token' => $owner['guest_token'],
            'product_id' => $product->id,
        ]);

        return true;
    }

    public function add(Request $request, int $productId): void
    {
        $product = Product::query()->where('is_unlaunched', false)->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages(['product_id' => 'Product not found.']);
        }

        $owner = $this->guestDevice->owner($request);

        if ($this->findOwnerItem($owner, $product->id) !== null) {
            return;
        }

        WishlistItem::query()->create([
            'user_id' => $owner['user_id'],
            'guest_token' => $owner['guest_token'],
            'product_id' => $product->id,
        ]);
    }

    public function remove(Request $request, int $itemId): void
    {
        $item = $this->ownerQuery($request)->whereKey($itemId)->first();

        if ($item === null) {
            abort(404);
        }

        $item->delete();
    }

    public function mergeGuestToUser(string $guestToken, User $user): void
    {
        DB::transaction(function () use ($guestToken, $user): void {
            $guestItems = WishlistItem::query()->where('guest_token', $guestToken)->get();

            foreach ($guestItems as $guestItem) {
                $exists = WishlistItem::query()
                    ->where('user_id', $user->id)
                    ->where('product_id', $guestItem->product_id)
                    ->exists();

                if ($exists) {
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
     * @return Builder<WishlistItem>
     */
    private function ownerQuery(Request $request)
    {
        $owner = $this->guestDevice->owner($request);

        return WishlistItem::query()->when(
            $owner['user_id'] !== null,
            fn ($query) => $query->where('user_id', $owner['user_id']),
            fn ($query) => $query->where('guest_token', $owner['guest_token']),
        );
    }

    /**
     * @param  array{user_id: ?int, guest_token: ?string}  $owner
     */
    private function findOwnerItem(array $owner, int $productId): ?WishlistItem
    {
        return WishlistItem::query()
            ->when(
                $owner['user_id'] !== null,
                fn ($query) => $query->where('user_id', $owner['user_id']),
                fn ($query) => $query->where('guest_token', $owner['guest_token']),
            )
            ->where('product_id', $productId)
            ->first();
    }
}
