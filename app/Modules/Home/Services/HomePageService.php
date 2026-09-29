<?php

namespace App\Modules\Home\Services;

use App\Core\Media\PublicUrl;
use App\Modules\Banner\Services\BannerStorefrontService;
use App\Modules\Product\Services\ProductService;

class HomePageService
{
    public const DealsLimit = 5;

    public function __construct(
        private readonly ProductService $productService,
        private readonly BannerStorefrontService $bannerStorefrontService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        $banners = $this->bannerStorefrontService->forHome();

        return [
            'quickCategories' => [
                [
                    'name' => 'Categories',
                    'slug' => 'all',
                    'short' => 'All',
                    'variant' => 'grid',
                    'tone' => 'all',
                    'href' => '/products',
                    'image' => $this->categoryImage('all_categories.png'),
                ],
                [
                    'name' => 'Western',
                    'slug' => 'western-wear',
                    'short' => 'We',
                    'tone' => 'fashion',
                    'image' => $this->categoryImage('western_wear.png'),
                ],
                [
                    'name' => 'Kids',
                    'slug' => 'kids-wear',
                    'short' => 'Ki',
                    'tone' => 'kids',
                    'image' => $this->categoryImage('kids_wear.png'),
                ],
                [
                    'name' => 'Beauty',
                    'slug' => 'beauty',
                    'short' => 'Be',
                    'tone' => 'beauty',
                    'image' => $this->categoryImage('beauty.png'),
                ],
                [
                    'name' => 'Home',
                    'slug' => 'home-decor',
                    'short' => 'Ho',
                    'tone' => 'home',
                    'image' => $this->categoryImage('home_decor.png'),
                ],
                [
                    'name' => 'Footwear',
                    'slug' => 'footwear',
                    'short' => 'Fo',
                    'tone' => 'footwear',
                    'image' => $this->categoryImage('footwear.png'),
                ],
                [
                    'name' => 'Bags',
                    'slug' => 'bags',
                    'short' => 'Ba',
                    'tone' => 'fashion',
                    'image' => $this->categoryImage('bags_and_accessories.png'),
                ],
            ],
            'offers' => [
                [
                    'name' => 'Bags & Accessories',
                    'slug' => 'bags',
                    'badge' => 'Sale upto 70% off',
                    'tone' => 'fashion',
                    'image' => $this->categoryImage('bags_and_accessories.png'),
                ],
                [
                    'name' => 'Footwear',
                    'slug' => 'footwear',
                    'badge' => 'From ₹9',
                    'tone' => 'footwear',
                    'image' => $this->categoryImage('footwear.png'),
                ],
                [
                    'name' => 'Western Wear',
                    'slug' => 'western-wear',
                    'badge' => 'Under ₹15',
                    'tone' => 'kids',
                    'image' => $this->categoryImage('western_wear.png'),
                ],
                [
                    'name' => 'Kids Wear',
                    'slug' => 'kids-wear',
                    'badge' => 'Upto 60% off',
                    'tone' => 'all',
                    'image' => $this->categoryImage('kids_wear.png'),
                ],
                [
                    'name' => 'Home Decor',
                    'slug' => 'home-decor',
                    'badge' => 'From ₹5',
                    'tone' => 'home',
                    'image' => $this->categoryImage('home_decor.png'),
                ],
                [
                    'name' => 'Beauty',
                    'slug' => 'beauty',
                    'badge' => 'Upto 50% off',
                    'tone' => 'beauty',
                    'image' => $this->categoryImage('beauty.png'),
                ],
            ],
            'products' => $this->productService->featured(4),
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

    private function categoryImage(string $filename): string
    {
        return (string) PublicUrl::for('assets/website/category/'.$filename);
    }
}
