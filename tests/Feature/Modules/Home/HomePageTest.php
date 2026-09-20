<?php

namespace Tests\Feature\Modules\Home;

use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Pages/Home')
            ->has('seo.title')
            ->has('seo.description')
            ->has('seo.keywords')
            ->has('quickCategories')
            ->has('offers')
            ->has('products')
            ->has('heroBanners')
            ->has('landscapeBanners')
            ->has('footerBanners')
            ->where('appName', config('app.name'))
        );
    }

    public function test_home_page_loads_active_banners_from_database(): void
    {
        Banner::factory()->create([
            'title' => 'Live Hero Banner',
            'subtitle' => 'From the database',
            'position' => BannerPosition::Hero,
            'style' => BannerStyle::Cinematic,
            'navigation_link' => BannerNavigationLink::Products,
            'button_text' => 'Shop live',
            'sort_order' => 1,
            'is_active' => true,
            'web_image_path' => 'assets/website/banners/hero_audio.png',
            'mobile_image_path' => 'assets/website/banners/hero_home.png',
        ]);

        Banner::factory()->create([
            'title' => 'Hidden Hero',
            'position' => BannerPosition::Hero,
            'is_active' => false,
        ]);

        Banner::factory()->create([
            'title' => 'Middle Promo',
            'position' => BannerPosition::Middle,
            'is_active' => true,
            'web_image_path' => 'assets/website/banners/promo_fashion.png',
        ]);

        Banner::factory()->create([
            'title' => 'Footer Deal',
            'position' => BannerPosition::Footer,
            'is_active' => true,
            'web_image_path' => 'assets/website/banners/promo_home.png',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home/Pages/Home')
                ->has('heroBanners', 1)
                ->where('heroBanners.0.title', 'Live Hero Banner')
                ->where('heroBanners.0.cta', 'Shop live')
                ->where('heroBanners.0.href', '/products')
                ->where('heroBanners.0.style', 'cinematic')
                ->has('landscapeBanners', 1)
                ->where('landscapeBanners.0.title', 'Middle Promo')
                ->has('footerBanners', 1)
                ->where('footerBanners.0.title', 'Footer Deal')
            );
    }

    public function test_offer_zone_page_lists_sale_products(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sale Headphones',
            'slug' => 'sale-headphones',
            'price' => 99,
            'sale_price' => 79,
            'is_unlaunched' => false,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Full Price Lamp',
            'slug' => 'full-price-lamp',
            'price' => 45,
            'sale_price' => null,
            'is_unlaunched' => false,
        ]);

        Banner::factory()->create([
            'title' => 'Offer Zone Live',
            'subtitle' => 'Admin managed offer banner',
            'position' => BannerPosition::OfferZone,
            'navigation_link' => BannerNavigationLink::Products,
            'button_text' => 'Shop deals',
            'is_active' => true,
            'web_image_path' => 'assets/website/banners/promo_home.png',
        ]);

        Banner::factory()->create([
            'title' => 'Hidden Offer Banner',
            'position' => BannerPosition::OfferZone,
            'is_active' => false,
        ]);

        $this->get(route('offers'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home/Pages/OfferZone')
                ->has('seo.title')
                ->has('products', 1)
                ->where('products.0.slug', 'sale-headphones')
                ->where('stats.count', 1)
                ->has('highlights')
                ->has('banners', 1)
                ->where('banners.0.title', 'Offer Zone Live')
                ->where('banners.0.cta', 'Shop deals')
            );
    }
}
