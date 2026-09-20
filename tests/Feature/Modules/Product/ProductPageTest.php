<?php

namespace Tests\Feature\Modules\Product;

use App\Modules\Category\Database\Seeders\CategorySeeder;
use App\Modules\Product\Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        $this->seed(ProductSeeder::class);
    }

    public function test_product_index_renders(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Product/Pages/Index')
            ->has('seo.title')
            ->has('products')
        );
    }

    public function test_product_show_renders_with_seo_and_images(): void
    {
        $response = $this->get(route('products.show', ['product' => 'wireless-headphones']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Product/Pages/Show')
            ->where('product.slug', 'wireless-headphones')
            ->has('product.images', 3)
            ->has('product.specs')
            ->where('product.lowStockThreshold', 10)
            ->has('seo.title')
            ->has('seo.description')
            ->has('seo.keywords')
            ->has('related')
        );
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $this->get(route('products.show', ['product' => 'missing-item']))
            ->assertNotFound();
    }
}
