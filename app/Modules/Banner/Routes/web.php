<?php

use App\Modules\Admin\Http\Middleware\EnsureUserIsAdmin;
use App\Modules\Banner\Http\Controllers\Admin\BannerController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function (): void {
        Route::resource('banners', BannerController::class)
            ->except(['show']);
    });
