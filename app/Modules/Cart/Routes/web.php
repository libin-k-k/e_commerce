<?php

use App\Modules\Cart\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::delete('/cart', [CartController::class, 'destroyMany'])->name('cart.destroy-many');
Route::post('/cart/wishlist', [CartController::class, 'moveToWishlist'])->name('cart.move-to-wishlist');
Route::patch('/cart/{item}', [CartController::class, 'update'])->whereNumber('item')->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->whereNumber('item')->name('cart.destroy');
