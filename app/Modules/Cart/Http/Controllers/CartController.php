<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Http\Requests\StoreCartItemRequest;
use App\Modules\Cart\Http\Requests\UpdateCartItemRequest;
use App\Modules\Cart\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): RedirectResponse
    {
        return redirect()
            ->route('home')
            ->with('open_sheet', 'cart');
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

        $redirect = back()->with('success', 'Added to cart.');

        if ($request->boolean('buy_now')) {
            return $redirect->with('open_sheet', 'cart');
        }

        return $redirect;
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
}
