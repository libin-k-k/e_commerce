<?php

namespace Tests\Feature\Modules\Product;

use App\Models\User;
use App\Modules\Account\Models\UserAddress;
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

    public function test_related_products_are_topped_up_from_other_categories(): void
    {
        $this->get(route('products.show', ['product' => 'minimal-desk-lamp']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('related', 3)
                ->where('related.0.slug', 'ceramic-mug-set')
                ->where('related', fn ($related) => ! collect($related)->contains('slug', 'minimal-desk-lamp'))
            );
    }

    public function test_product_show_shares_store_faqs_and_no_address_for_guests(): void
    {
        $this->get(route('products.show', ['product' => 'wireless-headphones']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('deliveryAddress', null)
                ->has('faqs.0.question')
                ->has('faqs.0.answer')
            );
    }

    public function test_product_show_delivers_to_the_default_address(): void
    {
        $customer = User::factory()->create(['name' => 'Libin K K']);
        UserAddress::query()->create([
            'user_id' => $customer->id,
            'label' => 'Work',
            'full_address' => '12 Office Road',
            'pincode' => '682001',
            'district' => 'Ernakulam',
            'state' => 'Kerala',
            'is_default' => false,
        ]);
        UserAddress::query()->create([
            'user_id' => $customer->id,
            'label' => 'Home',
            'full_address' => 'Kayamkulam',
            'pincode' => '690502',
            'district' => 'Alappuzha',
            'state' => 'Kerala',
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('products.show', ['product' => 'wireless-headphones']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('deliveryAddress', [
                    'label' => 'Home',
                    'name' => 'Libin K K',
                    'line' => 'Alappuzha, Kerala - 690502',
                ])
            );
    }

    public function test_unknown_product_returns_not_found(): void
    {
        $this->get(route('products.show', ['product' => 'missing-item']))
            ->assertNotFound();
    }
}
