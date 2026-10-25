<?php

use App\Modules\Home\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home/products', [HomeController::class, 'products'])->name('home.products');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
