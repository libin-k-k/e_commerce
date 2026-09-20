<?php

namespace Database\Factories;

use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'subtitle' => fake()->sentence(8),
            'position' => BannerPosition::Hero,
            'style' => BannerStyle::Cinematic,
            'web_image_path' => 'assets/website/banners/hero_home.png',
            'mobile_image_path' => 'assets/website/banners/hero_home.png',
            'navigation_link' => BannerNavigationLink::Products,
            'custom_url' => null,
            'category_slug' => null,
            'button_text' => 'Shop Now',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
