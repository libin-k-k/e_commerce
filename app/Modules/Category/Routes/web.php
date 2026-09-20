<?php

use App\Modules\Admin\Http\Middleware\EnsureUserIsAdmin;
use App\Modules\Category\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function (): void {
        Route::resource('categories', CategoryController::class)
            ->except(['show']);
    });
