<?php

namespace App\Modules\Product\Providers;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Observers\ProductObserver;
use App\Modules\Product\Repositories\Contracts\ProductAdminRepositoryInterface;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Repositories\EloquentProductRepository;
use App\Modules\Product\Repositories\ProductAdminRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(ProductAdminRepositoryInterface::class, ProductAdminRepository::class);
    }

    public function boot(): void
    {
        Product::observe(ProductObserver::class);

        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
