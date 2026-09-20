<?php

namespace App\Modules\Category\Providers;

use App\Modules\Category\Repositories\CategoryRepository;
use App\Modules\Category\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
