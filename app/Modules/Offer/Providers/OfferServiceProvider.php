<?php

namespace App\Modules\Offer\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OfferServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
