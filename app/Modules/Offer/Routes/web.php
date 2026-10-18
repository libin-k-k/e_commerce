<?php

use App\Modules\Admin\Http\Middleware\EnsureUserIsAdmin;
use App\Modules\Offer\Http\Controllers\Admin\OfferCampaignController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function (): void {
        Route::put('offers/settings', [OfferCampaignController::class, 'updateSettings'])
            ->name('offers.settings.update');
        Route::resource('offers', OfferCampaignController::class)->except(['show']);
    });
