<?php

namespace Tests\Feature\Modules\Admin;

use App\Models\User;
use App\Modules\Category\Models\Category;
use App\Modules\Offer\Models\OfferCampaign;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfferAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_offer_with_dates_countdown_and_four_products(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $products = Product::factory()->count(4)->create([
            'category_id' => $category->id,
            'price' => 100,
            'sale_price' => 60,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.offers.store'), [
                'title' => 'Deals for you',
                'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'countdown_enabled' => true,
                'is_active' => true,
                'sort_order' => 1,
                'product_ids' => $products->pluck('id')->all(),
            ])
            ->assertRedirect(route('admin.offers.index'));

        $campaign = OfferCampaign::query()->where('title', 'Deals for you')->first();
        $this->assertNotNull($campaign);
        $this->assertTrue($campaign->countdown_enabled);
        $this->assertCount(4, $campaign->products);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('dealCards.0.title', $category->name)
                ->where('dealCards.0.href', '/products?category='.rawurlencode($category->slug))
                ->where('dealCards.0.countdownEnabled', false)
                ->has('dealCards.0.products', 4)
                ->where('dealCards.0.products', function ($tiles) use ($products): bool {
                    return collect($tiles)->pluck('label')->sort()->values()->all()
                        === $products->pluck('name')->sort()->values()->all();
                })
                ->where('dealCards', fn ($cards): bool => collect($cards)->pluck('title')->doesntContain('Deals for you'))
            );

        $this->get(route('offers'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('products', 4)
                ->where('schedule.countdownEnabled', true)
            );
    }

    public function test_offer_card_rejects_more_than_four_products(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $products = Product::factory()->count(5)->create(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->from(route('admin.offers.create'))
            ->post(route('admin.offers.store'), [
                'title' => 'Too many',
                'is_active' => true,
                'product_ids' => $products->pluck('id')->all(),
            ])
            ->assertRedirect(route('admin.offers.create'))
            ->assertSessionHasErrors('product_ids');
    }

    public function test_home_hides_an_offer_that_has_not_started(): void
    {
        $category = Category::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        $product = Product::factory()->create(['category_id' => $category->id]);
        $campaign = OfferCampaign::query()->create([
            'title' => 'Future deals',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'countdown_enabled' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $campaign->products()->sync([$product->id => ['sort_order' => 0]]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('dealCards', fn ($cards): bool => collect($cards)->pluck('title')->doesntContain('Future deals'))
            );

        $this->get(route('offers'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('products', 0)
                ->where('schedule.countdownEnabled', false)
            );
    }

    public function test_admin_can_set_the_offer_page_theme_and_banner_countdown(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.offers.settings.update'), [
                'section_title' => 'Festive picks',
                'section_subtitle' => 'Season specials',
                'eyebrow' => 'Festival',
                'theme' => 'festival',
                'countdown_enabled' => true,
                'countdown_ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'is_enabled' => true,
            ])
            ->assertRedirect(route('admin.offers.index'));

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('offerPage.title', 'Festive picks')
                ->where('offerPage.subtitle', 'Season specials')
                ->where('offerPage.theme', 'festival')
                ->where('offerPage.countdownEnabled', true)
            );

        $this->get(route('offers'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('offerPage.eyebrow', 'Festival')
                ->where('offerPage.theme', 'festival')
                ->where('schedule.countdownEnabled', true)
            );
    }

    public function test_offer_banner_countdown_requires_an_end_time(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.offers.index'))
            ->put(route('admin.offers.settings.update'), [
                'section_title' => 'Offer Zone',
                'eyebrow' => 'Offer Zone',
                'theme' => 'classic',
                'countdown_enabled' => true,
                'is_enabled' => true,
            ])
            ->assertRedirect(route('admin.offers.index'))
            ->assertSessionHasErrors('countdown_ends_at');
    }
}
