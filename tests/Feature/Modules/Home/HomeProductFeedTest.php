<?php

namespace Tests\Feature\Modules\Home;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductSize;
use App\Modules\Product\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeProductFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_exposes_the_scroll_feed_endpoint(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home/Pages/Home')
                ->where('homeProductsUrl', route('home.products'))
                ->missing('products')
            );
    }

    public function test_home_feed_loads_one_database_page_at_a_time(): void
    {
        $category = Category::factory()->create();

        foreach (range(1, 14) as $order) {
            Product::factory()->create([
                'category_id' => $category->id,
                'name' => 'Feed '.$order,
                'slug' => 'feed-'.$order,
                'sku' => 'FEED-'.$order,
                'sort_order' => $order,
                'price' => 100 + $order,
                'sale_price' => null,
                'rating' => 4.5,
            ]);
        }

        Product::factory()->unlaunched()->create([
            'category_id' => $category->id,
            'name' => 'Hidden feed',
            'slug' => 'hidden-feed',
            'sku' => 'FEED-HIDDEN',
            'sort_order' => 0,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $first = $this->getJson(route('home.products'));

        $first->assertOk()
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.perPage', 12)
            ->assertJsonPath('pagination.lastPage', 2)
            ->assertJsonPath('total', 14)
            ->assertJsonCount(12, 'products')
            ->assertJsonPath('products.0.slug', 'feed-1');

        $this->assertNotContains('hidden-feed', collect($first->json('products'))->pluck('slug'));
        $this->assertTrue(collect(DB::getQueryLog())->contains(function (array $query): bool {
            $sql = strtolower($query['query']);

            return str_contains($sql, 'limit 12')
                || (str_contains($sql, 'limit') && in_array(12, $query['bindings'], true));
        }));

        $second = $this->getJson(route('home.products', ['page' => 2]));

        $second->assertOk()
            ->assertJsonPath('pagination.page', 2)
            ->assertJsonCount(2, 'products')
            ->assertJsonPath('products.0.slug', 'feed-13')
            ->assertJsonPath('products.1.slug', 'feed-14');
    }

    public function test_home_feed_filters_category_rating_size_and_display_price(): void
    {
        $bags = Category::factory()->create(['name' => 'Bags', 'slug' => 'bags']);
        $shoes = Category::factory()->create(['name' => 'Shoes', 'slug' => 'shoes']);

        Product::factory()->create([
            'category_id' => $bags->id,
            'name' => 'Sale tote',
            'slug' => 'sale-tote',
            'sku' => 'SALE-TOTE',
            'price' => 200,
            'sale_price' => 80,
            'rating' => 4.6,
            'sort_order' => 1,
        ]);
        Product::factory()->create([
            'category_id' => $bags->id,
            'name' => 'Full price tote',
            'slug' => 'full-price-tote',
            'sku' => 'FULL-TOTE',
            'price' => 150,
            'sale_price' => null,
            'rating' => 3.2,
            'sort_order' => 2,
        ]);
        $variantProduct = Product::factory()->create([
            'category_id' => $bags->id,
            'name' => 'Variant bag',
            'slug' => 'variant-bag',
            'sku' => 'VARIANT-BAG',
            'price' => 40,
            'sale_price' => null,
            'rating' => 4.8,
            'sort_order' => 3,
        ]);
        ProductVariant::query()->create([
            'product_id' => $variantProduct->id,
            'combo_key' => 's:0|c:0',
            'sku' => 'VARIANT-BAG-1',
            'price' => 900,
            'sale_price' => null,
            'stock' => 4,
        ]);
        ProductSize::query()->create([
            'product_id' => $variantProduct->id,
            'name' => 'Large',
            'sort_order' => 1,
        ]);
        Product::factory()->create([
            'category_id' => $shoes->id,
            'name' => 'Cheap shoe',
            'slug' => 'cheap-shoe',
            'sku' => 'CHEAP-SHOE',
            'price' => 70,
            'sale_price' => null,
            'rating' => 4.9,
            'sort_order' => 1,
        ]);

        $this->assertSame(
            ['sale-tote', 'full-price-tote', 'variant-bag'],
            $this->slugs(['category' => 'bags']),
        );
        $this->assertSame(
            ['cheap-shoe', 'sale-tote'],
            $this->slugs(['price' => 'under-100', 'sort' => 'price_asc']),
        );
        $this->assertSame(
            ['sale-tote', 'variant-bag'],
            $this->slugs(['category' => 'bags', 'rating' => 4]),
        );
        $this->assertSame(
            ['variant-bag'],
            $this->slugs(['size' => 'Large']),
        );
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<string>
     */
    private function slugs(array $query): array
    {
        $response = $this->getJson(route('home.products', $query));
        $response->assertOk();

        return collect($response->json('products'))->pluck('slug')->all();
    }
}
