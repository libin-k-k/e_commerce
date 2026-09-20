<?php

namespace App\Modules\Banner\Database\Seeders;

use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Wireless sound, all-day comfort',
                'subtitle' => 'Shop headphones built for work, travel, and weekends.',
                'position' => BannerPosition::Hero,
                'style' => BannerStyle::Cinematic,
                'web_image_path' => 'assets/website/banners/hero_audio.png',
                'mobile_image_path' => 'assets/website/banners/hero_audio.png',
                'navigation_link' => BannerNavigationLink::Custom,
                'custom_url' => '/products/wireless-headphones',
                'button_text' => 'Shop audio',
                'sort_order' => 1,
            ],
            [
                'title' => 'Light up your workspace',
                'subtitle' => 'Minimal desk lamps with soft, adjustable light.',
                'position' => BannerPosition::Hero,
                'style' => BannerStyle::DarkSplit,
                'web_image_path' => 'assets/website/banners/hero_home.png',
                'mobile_image_path' => 'assets/website/banners/hero_home.png',
                'navigation_link' => BannerNavigationLink::Custom,
                'custom_url' => '/products/minimal-desk-lamp',
                'button_text' => 'Shop home',
                'sort_order' => 2,
            ],
            [
                'title' => 'Western wear picks',
                'subtitle' => 'Fresh styles for every day.',
                'position' => BannerPosition::Middle,
                'style' => BannerStyle::Gradient,
                'web_image_path' => 'assets/website/banners/promo_fashion.png',
                'mobile_image_path' => 'assets/website/banners/promo_fashion.png',
                'navigation_link' => BannerNavigationLink::Category,
                'category_slug' => 'western-wear',
                'button_text' => 'Browse fashion',
                'sort_order' => 1,
            ],
            [
                'title' => 'Beauty essentials',
                'subtitle' => 'Glow-ready deals starting now.',
                'position' => BannerPosition::Middle,
                'style' => BannerStyle::Cinematic,
                'web_image_path' => 'assets/website/banners/promo_beauty.png',
                'mobile_image_path' => 'assets/website/banners/promo_beauty.png',
                'navigation_link' => BannerNavigationLink::Category,
                'category_slug' => 'beauty',
                'button_text' => 'Shop beauty',
                'sort_order' => 2,
            ],
            [
                'title' => 'Weekend deals are live',
                'subtitle' => 'Save on everyday essentials with limited-time offers.',
                'position' => BannerPosition::Footer,
                'style' => BannerStyle::Gradient,
                'web_image_path' => 'assets/website/banners/promo_home.png',
                'mobile_image_path' => 'assets/website/banners/promo_home.png',
                'navigation_link' => BannerNavigationLink::Sale,
                'button_text' => 'Get Offer',
                'sort_order' => 1,
            ],
            [
                'title' => 'OneCart',
                'subtitle' => 'Deals worth opening - every product with an active discount, in one place.',
                'position' => BannerPosition::OfferZone,
                'style' => BannerStyle::DarkSplit,
                'web_image_path' => 'assets/website/banners/promo_home.png',
                'mobile_image_path' => 'assets/website/banners/promo_home.png',
                'navigation_link' => BannerNavigationLink::Products,
                'button_text' => 'Browse all products',
                'sort_order' => 1,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::query()->updateOrCreate(
                [
                    'title' => $banner['title'],
                    'position' => $banner['position']->value,
                ],
                [
                    ...$banner,
                    'is_active' => true,
                ],
            );
        }
    }
}
