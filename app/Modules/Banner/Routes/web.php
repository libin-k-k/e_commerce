<?php

use App\Modules\Admin\Http\Middleware\EnsureUserIsAdmin;
use App\Modules\Banner\Http\Controllers\Admin\BannerController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function (): void {
        Route::post('banners/reorder', [BannerController::class, 'reorder'])->name('banners.reorder');
        Route::put('banners/sections', [BannerController::class, 'updateSections'])->name('banners.sections.update');
        Route::resource('banners', BannerController::class)
            ->except(['show']);
    });
