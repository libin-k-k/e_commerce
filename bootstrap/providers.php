<?php

use App\Modules\Account\Providers\AccountServiceProvider;
use App\Modules\Admin\Providers\AdminServiceProvider;
use App\Modules\Banner\Providers\BannerServiceProvider;
use App\Modules\Cart\Providers\CartServiceProvider;
use App\Modules\Category\Providers\CategoryServiceProvider;
use App\Modules\Help\Providers\HelpServiceProvider;
use App\Modules\Home\Providers\HomeServiceProvider;
use App\Modules\Product\Providers\ProductServiceProvider;
use App\Modules\Wishlist\Providers\WishlistServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    HomeServiceProvider::class,
    ProductServiceProvider::class,
    HelpServiceProvider::class,
    AccountServiceProvider::class,
    AdminServiceProvider::class,
    BannerServiceProvider::class,
    CategoryServiceProvider::class,
    CartServiceProvider::class,
    WishlistServiceProvider::class,
];
