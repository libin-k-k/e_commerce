<?php

use App\Modules\Admin\Http\Middleware\EnsureUserIsAdmin;
use App\Modules\Product\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Modules\Product\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->where('product', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
    ->name('products.show');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function (): void {
        Route::resource('products', AdminProductController::class)
            ->except(['show']);
    });
