<?php

namespace App\Modules\Home\Services;

use App\Core\Media\PublicUrl;
use App\Modules\Banner\Services\BannerStorefrontService;
use App\Modules\Product\Services\ProductService;

class HomePageService
{
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
                    'href' => '/products',
                    'image' => $this->categoryImage('all_categories.png'),
                ],
                [
                    'name' => 'Western',
                    'slug' => 'western-wear',
                    'short' => 'We',
                    'image' => $this->categoryImage('western_wear.png'),
                ],
                [
                    'name' => 'Kids',
                    'slug' => 'kids-wear',
                    'short' => 'Ki',
                    'image' => $this->categoryImage('kids_wear.png'),
                ],
                [
                    'name' => 'Beauty',
                    'slug' => 'beauty',
                    'short' => 'Be',
                    'image' => $this->categoryImage('beauty.png'),
                ],
                [
                    'name' => 'Home',
                    'slug' => 'home-decor',
                    'short' => 'Ho',
                    'image' => $this->categoryImage('home_decor.png'),
                ],
                [
                    'name' => 'Footwear',
                    'slug' => 'footwear',
                    'short' => 'Fo',
                    'image' => $this->categoryImage('footwear.png'),
                ],
                [
                    'name' => 'Bags',
                    'slug' => 'bags',
                    'short' => 'Ba',
                    'image' => $this->categoryImage('bags_and_accessories.png'),
                ],
            ],
            'offers' => [
                [
                    'name' => 'Bags & Accessories',
                    'slug' => 'bags',
                    'badge' => 'Sale upto 70% off',
                    'image' => $this->categoryImage('bags_and_accessories.png'),
                ],
                [
                    'name' => 'Footwear',
                    'slug' => 'footwear',
                    'badge' => 'From ₹9',
                    'image' => $this->categoryImage('footwear.png'),
                ],
                [
                    'name' => 'Western Wear',
                    'slug' => 'western-wear',
                    'badge' => 'Under ₹15',
                    'image' => $this->categoryImage('western_wear.png'),
                ],
                [
                    'name' => 'Kids Wear',
                    'slug' => 'kids-wear',
                    'badge' => 'Upto 60% off',
                    'image' => $this->categoryImage('kids_wear.png'),
                ],
                [
                    'name' => 'Home Decor',
                    'slug' => 'home-decor',
                    'badge' => 'From ₹5',
                    'image' => $this->categoryImage('home_decor.png'),
                ],
                [
                    'name' => 'Beauty',
                    'slug' => 'beauty',
                    'badge' => 'Upto 50% off',
                    'image' => $this->categoryImage('beauty.png'),
                ],
            ],
            'products' => $this->productService->featured(4),
            'heroBanners' => $banners['heroBanners'],
            'landscapeBanners' => $banners['landscapeBanners'],
            'footerBanners' => $banners['footerBanners'],
            'benefits' => [
                [
                    'title' => 'Fast delivery',
                    'text' => 'Trackable shipping with clear delivery windows.',
                ],
                [
                    'title' => 'Easy returns',
                    'text' => 'Hassle-free returns on eligible products.',
                ],
                [
                    'title' => 'Secure checkout',
                    'text' => 'Protected payments with order confirmation you can trust.',
                ],
            ],
        ];
    }

    private function categoryImage(string $filename): string
    {
        return (string) PublicUrl::for('assets/website/category/'.$filename);
    }
}
