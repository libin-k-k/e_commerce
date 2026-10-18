<?php

use App\Modules\Admin\Http\Middleware\EnsureUserIsAdmin;
use App\Modules\Coupon\Http\Controllers\Admin\CouponController;
use App\Modules\Coupon\Http\Controllers\CartCouponController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function (): void {
        Route::resource('coupons', CouponController::class)->except(['show']);
    });

Route::post('/cart/coupon', [CartCouponController::class, 'store'])->name('cart.coupon.store');
Route::delete('/cart/coupon', [CartCouponController::class, 'destroy'])->name('cart.coupon.destroy');
