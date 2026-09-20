<?php

namespace App\Modules\Home\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Banner\Services\BannerStorefrontService;
use App\Modules\Home\Services\HomePageService;
use App\Modules\Product\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomePageService $homePageService,
        private readonly ProductService $productService,
        private readonly BannerStorefrontService $bannerStorefrontService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        $seo = $this->seoJsonLoader->load('Modules/Home/home');
        $content = $this->homePageService->content();

        return Inertia::render('Home/Pages/Home', [
            'seo' => $seo,
            ...$content,
        ]);
    }

    public function offers(): Response
    {
        $products = $this->productService->onSale();
        $banners = $this->bannerStorefrontService->forOfferZone();

        return Inertia::render('Home/Pages/OfferZone', [
            'seo' => $this->seoJsonLoader->load('Modules/Home/offers'),
            'products' => $products,
            'banners' => $banners,
            'stats' => [
                'count' => count($products),
                'label' => count($products) === 1 ? 'deal live' : 'deals live',
            ],
            'highlights' => $this->homePageService->content()['offers'],
        ]);
    }
}
