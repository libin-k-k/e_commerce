<?php

namespace Tests\Feature\Modules\Product;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_matching_products_are_returned_with_card_fields(): void
    {
        $category = Category::factory()->create(['name' => 'Audio']);
        Product::factory()->for($category)->create([
            'name' => 'Studio Headphones',
            'slug' => 'studio-headphones',
            'price' => 100,
            'sale_price' => 70,
        ]);
        Product::factory()->for($category)->create(['name' => 'Desk Lamp', 'short_description' => 'Warm light']);

        $response = $this->getJson(route('products.suggestions', ['q' => 'headph']));

        $response->assertOk()
            ->assertJsonPath('query', 'headph')
            ->assertJsonPath('matched', true)
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.slug', 'studio-headphones')
            ->assertJsonPath('products.0.categoryName', 'Audio')
            ->assertJsonStructure(['products' => [[
                'id', 'slug', 'name', 'image', 'price', 'compareAtPrice', 'badge',
                'isNew', 'rating', 'categoryName', 'inStock', 'hasVariants',
            ]]]);
    }

    public function test_suggestions_are_capped_while_total_counts_every_match(): void
    {
        Product::factory()->count(ProductService::SuggestionLimit + 2)->sequence(
            fn ($sequence): array => ['name' => 'Cotton Shirt '.$sequence->index],
        )->create();

        $response = $this->getJson(route('products.suggestions', ['q' => 'cotton']));

        $response->assertOk()
            ->assertJsonPath('total', ProductService::SuggestionLimit + 2)
            ->assertJsonCount(ProductService::SuggestionLimit, 'products');
    }

    public function test_unlaunched_products_are_excluded(): void
    {
        Product::factory()->unlaunched()->create(['name' => 'Secret Sneakers']);

        $this->getJson(route('products.suggestions', ['q' => 'sneakers']))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonCount(0, 'products');
    }

    public function test_short_or_missing_terms_fall_back_to_featured_products(): void
    {
        Product::factory()->create(['name' => 'Alpha Watch', 'sort_order' => 2]);
        Product::factory()->create(['name' => 'Beta Scarf', 'sort_order' => 1]);
        Product::factory()->unlaunched()->create(['name' => 'Hidden Ring']);

        $this->getJson(route('products.suggestions', ['q' => ' a ']))
            ->assertOk()
            ->assertJsonPath('query', 'a')
            ->assertJsonPath('matched', false)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('products.0.name', 'Beta Scarf')
            ->assertJsonPath('products.1.name', 'Alpha Watch');

        $this->getJson(route('products.suggestions'))
            ->assertOk()
            ->assertJsonPath('query', '')
            ->assertJsonPath('matched', false)
            ->assertJsonCount(2, 'products');
    }

    public function test_like_wildcards_in_the_term_are_matched_literally(): void
    {
        Product::factory()->create(['name' => 'Plain Bottle', 'short_description' => 'Steel', 'sku' => 'SKU-1']);

        $this->getJson(route('products.suggestions', ['q' => '%%']))
            ->assertOk()
            ->assertJsonPath('total', 0);
    }
}
