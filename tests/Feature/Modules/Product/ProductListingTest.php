<?php

namespace Tests\Feature\Modules\Product;

use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Models\Banner;
use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductListingTest extends TestCase
{
    use RefreshDatabase;

    private Category $women;

    private Category $dresses;

    private Category $tops;

    protected function setUp(): void
    {
        parent::setUp();

        $this->women = Category::factory()->create(['name' => 'Women Fashion', 'slug' => 'women-fashion', 'sort_order' => 1]);
        $this->dresses = Category::factory()->childOf($this->women)->create(['name' => 'Dresses', 'slug' => 'dresses', 'sort_order' => 1]);
        $this->tops = Category::factory()->childOf($this->women)->create(['name' => 'Tops', 'slug' => 'tops', 'sort_order' => 2]);
    }

    public function test_main_category_lists_its_products_and_sub_category_chips(): void
    {
        $this->product('floral-dress', ['subcategory_id' => $this->dresses->id]);
        $this->product('puff-top', ['subcategory_id' => $this->tops->id]);
        $this->product('desk-lamp', ['category_id' => Category::factory()->create()->id]);

        $this->get(route('products.index', ['category' => 'women-fashion']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Product/Pages/Index')
                ->where('total', 2)
                ->where('category.name', 'Women Fashion')
                ->where('seo.title', 'Shop Women Fashion online')
                ->where('chips.allSlug', 'women-fashion')
                ->where('chips.activeSlug', null)
                ->where('chips.items.0.slug', 'dresses')
                ->where('chips.items.1.slug', 'tops')
                ->where('filters.sort', 'popular')
            );
    }

    public function test_sub_category_keeps_sibling_chips_and_marks_itself_active(): void
    {
        $this->product('floral-dress', ['subcategory_id' => $this->dresses->id]);
        $this->product('puff-top', ['subcategory_id' => $this->tops->id]);

        $this->get(route('products.index', ['category' => 'tops']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('total', 1)
                ->where('products.0.slug', 'puff-top')
                ->where('category.parentSlug', 'women-fashion')
                ->where('chips.allSlug', 'women-fashion')
                ->where('chips.activeSlug', 'tops')
                ->has('chips.items', 2)
            );
    }

    public function test_search_price_rating_and_size_filters_combine(): void
    {
        $match = $this->product('summer-dress', ['name' => 'Summer Dress', 'price' => 80, 'rating' => 4.5]);
        ProductSize::query()->create(['product_id' => $match->id, 'name' => 'M', 'sort_order' => 0]);

        $this->product('summer-gown', ['name' => 'Summer Gown', 'price' => 800, 'rating' => 4.8]);
        $this->product('summer-tee', ['name' => 'Summer Tee', 'price' => 60, 'rating' => 3.2]);
        $this->product('winter-coat', ['name' => 'Winter Coat', 'price' => 70, 'rating' => 4.9]);

        $this->get(route('products.index', ['q' => 'summer', 'price' => 'under-100', 'rating' => 4, 'size' => 'M']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('total', 1)
                ->where('products.0.slug', 'summer-dress')
                ->where('filters.price', 'under-100')
                ->where('filters.rating', 4)
                ->where('filterOptions.sizes', ['M'])
            );
    }

    public function test_price_sort_uses_the_effective_sale_price(): void
    {
        $this->product('mid', ['price' => 300]);
        $this->product('cheap-after-sale', ['price' => 900, 'sale_price' => 150]);
        $this->product('expensive', ['price' => 600]);

        $this->get(route('products.index', ['sort' => 'price_asc']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.0.slug', 'cheap-after-sale')
                ->where('products.0.discountPercent', 83)
                ->where('products.1.slug', 'mid')
                ->where('products.2.slug', 'expensive')
            );
    }

    public function test_unknown_filter_values_are_ignored(): void
    {
        $this->product('floral-dress');

        $this->get('/products?sort=cheapest&price=free&rating=9&category=missing')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('total', 1)
                ->where('category', null)
                ->where('filters.sort', 'popular')
                ->where('filters.price', null)
                ->where('filters.rating', null)
            );
    }

    public function test_results_are_paginated_and_out_of_range_pages_clamp_to_the_last(): void
    {
        foreach (range(1, 14) as $number) {
            $this->product('item-'.$number, ['sort_order' => $number]);
        }

        $this->get(route('products.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('total', 14)
                ->has('products', 2)
                ->where('products.0.slug', 'item-13')
                ->where('pagination.page', 2)
                ->where('pagination.lastPage', 2)
                ->where('pagination.from', 13)
                ->where('pagination.to', 14)
                ->where('filters.per_page', 12)
            );

        $this->get('/products?page=99&per_page=24')
            ->assertInertia(fn (Assert $page) => $page
                ->has('products', 14)
                ->where('pagination.page', 1)
                ->where('pagination.perPage', 24)
            );
    }

    public function test_colour_and_custom_price_range_filters_combine(): void
    {
        $match = $this->product('red-dress', ['price' => 250]);
        $match->colors()->create(['name' => 'Red', 'hex' => '#ff0000', 'sort_order' => 0]);

        $tooCheap = $this->product('red-scarf', ['price' => 90]);
        $tooCheap->colors()->create(['name' => 'Red', 'hex' => '#ff0000', 'sort_order' => 0]);

        $wrongColour = $this->product('blue-dress', ['price' => 260]);
        $wrongColour->colors()->create(['name' => 'Blue', 'hex' => '#0000ff', 'sort_order' => 0]);

        $this->get('/products?color=Red&min_price=500&max_price=100')
            ->assertInertia(fn (Assert $page) => $page
                ->where('total', 1)
                ->where('products.0.slug', 'red-dress')
                ->where('filters.color', 'Red')
                ->where('filters.min_price', 100)
                ->where('filters.max_price', 500)
            );
    }

    public function test_sidebar_facets_count_launched_products(): void
    {
        $dress = $this->product('floral-dress', ['subcategory_id' => $this->dresses->id, 'price' => 1450, 'rating' => 4.6]);
        $dress->colors()->create(['name' => 'Pink', 'hex' => '#ffc0cb', 'sort_order' => 0]);
        $this->product('puff-top', ['subcategory_id' => $this->tops->id, 'price' => 300, 'rating' => 3.4]);
        $this->product('hidden-top', ['subcategory_id' => $this->tops->id, 'is_unlaunched' => true]);

        $this->get(route('products.index', ['category' => 'women-fashion']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('categoryTree.total', 2)
                ->where('categoryTree.items.0.slug', 'women-fashion')
                ->where('categoryTree.items.0.count', 2)
                ->where('categoryTree.items.0.children.0.count', 1)
                ->where('categoryTree.items.0.children.1.count', 1)
                ->where('filterOptions.ratings.0.count', 1)
                ->where('filterOptions.ratings.1.count', 2)
                ->where('filterOptions.colors', [['name' => 'Pink', 'hex' => '#ffc0cb']])
                ->where('filterOptions.priceCeiling', 1500)
            );
    }

    public function test_active_product_listing_banner_is_shared(): void
    {
        Banner::factory()->create([
            'title' => 'Stay Stylish Everyday',
            'position' => BannerPosition::ProductListing,
        ]);
        Banner::factory()->create(['title' => 'Home hero only']);

        $this->get(route('products.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('banner.title', 'Stay Stylish Everyday')
            );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(string $slug, array $attributes = []): Product
    {
        return Product::factory()->create([
            'category_id' => $this->women->id,
            'slug' => $slug,
            ...$attributes,
        ]);
    }
}
