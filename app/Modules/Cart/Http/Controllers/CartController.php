<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Cart\Http\Requests\CartItemsRequest;
use App\Modules\Cart\Http\Requests\StoreCartItemRequest;
use App\Modules\Cart\Http\Requests\UpdateCartItemRequest;
use App\Modules\Cart\Services\CartService;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public const RecommendationLimit = 6;

    public function __construct(
        private readonly CartService $cart,
        private readonly ProductRepositoryInterface $products,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Cart/Pages/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Cart/index'),
            'delivery' => $this->cart->deliveryRule(),
            'recommendations' => $this->products->recommended(
                $this->cart->productIds($request),
                self::RecommendationLimit,
            ),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $this->cart->add(
            $request,
            (int) $request->validated('product_id'),
            (int) ($request->validated('quantity') ?? 1),
            $request->validated('product_variant_id') !== null
                ? (int) $request->validated('product_variant_id')
                : null,
        );

        if ($request->boolean('buy_now')) {
            return redirect()->route('cart.index')->with('success', 'Added to cart.');
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(UpdateCartItemRequest $request, int $item): RedirectResponse
    {
        $this->cart->updateQuantity($request, $item, (int) $request->validated('quantity'));

        return back()->with('success', 'Cart updated.')->with('open_sheet', 'cart');
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $this->cart->remove($request, $item);

        return back()->with('success', 'Item removed from cart.')->with('open_sheet', 'cart');
    }

    public function destroyMany(CartItemsRequest $request): RedirectResponse
    {
        $removed = $this->cart->removeMany($request, $request->itemIds());

        return back()->with('success', $removed.' '.Str::plural('item', $removed).' removed from cart.');
    }

    public function moveToWishlist(CartItemsRequest $request): RedirectResponse
    {
        $moved = $this->cart->moveToWishlist($request, $request->itemIds());

        return back()->with('success', $moved.' '.Str::plural('item', $moved).' moved to wishlist.');
    }
}
