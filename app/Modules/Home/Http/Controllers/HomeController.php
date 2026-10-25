<?php

namespace App\Modules\Home\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Banner\Services\BannerStorefrontService;
use App\Modules\Home\Services\HomePageService;
use App\Modules\Offer\Services\OfferStorefrontService;
use App\Modules\Product\Http\Requests\ProductIndexRequest;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomePageService $homePageService,
        private readonly ProductService $productService,
        private readonly BannerStorefrontService $bannerStorefrontService,
        private readonly OfferStorefrontService $offerStorefrontService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        $seo = $this->seoJsonLoader->load('Modules/Home/home');
        $content = $this->homePageService->content();

        return Inertia::render('Home/Pages/Home', [
            'seo' => $seo,
            ...$content,
            'homeProductsUrl' => route('home.products'),
        ]);
    }

    public function products(ProductIndexRequest $request): JsonResponse
    {
        return response()->json($this->productService->homeFeed($request->filters()));
    }

    public function offers(): Response
    {
        $page = $this->offerStorefrontService->hasCampaigns()
            ? $this->offerStorefrontService->page()
            : [
                'products' => $this->productService->onSale(),
                'schedule' => [
                    'countdownEnabled' => false,
                    'endsAt' => null,
                    'startsAt' => null,
                ],
            ];
        $presentation = $this->offerStorefrontService->presentation();
        $schedule = $presentation['countdownEnabled']
            ? [
                'countdownEnabled' => true,
                'endsAt' => $presentation['endsAt'],
                'startsAt' => null,
            ]
            : $page['schedule'];
        $products = $page['products'];
        $banners = $this->bannerStorefrontService->forOfferZone();

        return Inertia::render('Home/Pages/OfferZone', [
            'seo' => $this->seoJsonLoader->load('Modules/Home/offers'),
            'products' => $products,
            'schedule' => $schedule,
            'offerPage' => $presentation,
            'banners' => $banners,
            'stats' => [
                'count' => count($products),
                'label' => count($products) === 1 ? 'deal live' : 'deals live',
            ],
            'highlights' => $this->homePageService->content()['offers'],
        ]);
    }
}
