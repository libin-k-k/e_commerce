<?php

namespace Tests\Feature;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SitemapGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_launched_products_and_skips_unlaunched(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'wireless-headphones',
            'is_unlaunched' => false,
        ]);

        Product::factory()->unlaunched()->create([
            'category_id' => $category->id,
            'slug' => 'hidden-draft',
        ]);

        $this->artisan('sitemap:generate')->assertSuccessful();

        $path = public_path('sitemap.xml');
        $this->assertFileExists($path);

        $xml = File::get($path);
        $this->assertStringContainsString(route('products.index'), $xml);
        $this->assertStringContainsString(route('products.show', ['product' => 'wireless-headphones']), $xml);
        $this->assertStringNotContainsString('hidden-draft', $xml);
    }

    public function test_saving_product_refreshes_sitemap(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'desk-lamp',
            'is_unlaunched' => false,
        ]);

        $xml = File::get(public_path('sitemap.xml'));
        $this->assertStringContainsString(route('products.show', ['product' => 'desk-lamp']), $xml);
    }
}
