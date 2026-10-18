<?php

namespace App\Modules\Coupon\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Services\CartService;
use App\Modules\Coupon\Services\CouponCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartCouponController extends Controller
{
    public function __construct(
        private readonly CouponCartService $coupons,
        private readonly CartService $cart,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
        ]);

        $items = $this->cart->items($request)->all();
        $subtotal = round(collect($items)->sum('line_total'), 2);

        $this->coupons->apply($request, $data['code'], $items, $subtotal);

        return back()
            ->with('success', 'Coupon applied.')
            ->with('coupon_celebration', true);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->coupons->remove($request);

        return back()->with('success', 'Coupon removed.');
    }
}
