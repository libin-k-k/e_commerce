<?php

namespace App\Modules\Home\Services;

use App\Modules\Banner\Services\BannerStorefrontService;
use App\Modules\Category\Services\CategoryStorefrontService;
use App\Modules\Offer\Services\OfferStorefrontService;
use App\Modules\Product\Services\ProductService;

class HomePageService
{
    public const DealsLimit = 5;

    public function __construct(
        private readonly ProductService $productService,
        private readonly BannerStorefrontService $bannerStorefrontService,
        private readonly OfferStorefrontService $offerStorefrontService,
        private readonly CategoryStorefrontService $categoryStorefrontService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        $banners = $this->bannerStorefrontService->forHome();
        $categoryShowcases = $this->productService->featuredByCategory();

        return [
            'quickCategories' => $this->categoryStorefrontService->quickCategories(),
            'offers' => $this->categoryStorefrontService->offerHighlights(),
            'categoryShowcases' => $categoryShowcases,
            'dealCards' => $this->offerStorefrontService->cardsFromCategories($categoryShowcases),
            'offerPage' => $this->offerStorefrontService->presentation(),
            'deals' => array_slice($this->productService->onSale(), 0, self::DealsLimit),
            'dealsBanner' => $banners['dealsBanner'],
            'heroBanners' => $banners['heroBanners'],
            'landscapeBanners' => $banners['landscapeBanners'],
            'footerBanners' => $banners['footerBanners'],
            'benefits' => [
                [
                    'icon' => 'delivery',
                    'title' => 'Fast Delivery',
                    'text' => 'Trackable shipping',
                ],
                [
                    'icon' => 'returns',
                    'title' => 'Easy Returns',
                    'text' => 'Hassle-free returns',
                ],
                [
                    'icon' => 'secure',
                    'title' => 'Secure Payment',
                    'text' => '100% safe & secure',
                ],
                [
                    'icon' => 'support',
                    'title' => '24/7 Support',
                    'text' => "We're here to help",
                ],
            ],
        ];
    }
}
