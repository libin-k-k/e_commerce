<?php

namespace App\Modules\Product\Observers;

use App\Core\Seo\SitemapService;
use App\Modules\Product\Models\Product;

class ProductObserver
{
    public function __construct(private readonly SitemapService $sitemap) {}

    public function saved(Product $product): void
    {
        $this->sitemap->refresh();
    }

    public function deleted(Product $product): void
    {
        $this->sitemap->refresh();
    }
}
