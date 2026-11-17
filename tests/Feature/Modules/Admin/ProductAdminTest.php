<?php

namespace Tests\Feature\Modules\Admin;

use App\Models\User;
use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_products_index(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Demo Product']);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Pages/Products/Index')
                ->has('products', 1)
                ->where('products.0.name', 'Demo Product')
            );
    }

    public function test_admin_can_create_product_with_size_color_matrix(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Western Wear', 'slug' => 'western-wear']);
        $sub = Category::factory()->childOf($category)->create(['name' => 'Tops', 'slug' => 'western-tops']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Wireless headphones',
                'slug' => 'wireless-headphones',
                'sku' => 'SKU-HEAD-100',
                'short_description' => 'Clear sound',
                'description' => '<h2>Features</h2><p>Rich <strong>audio</strong> with <em>deep</em> bass.</p>',
                'price' => 99,
                'sale_price' => 79,
                'stock' => 0,
                'rating' => 4.5,
                'category_id' => $category->id,
                'subcategory_id' => $sub->id,
                'is_unlaunched' => false,
                'sort_order' => 1,
                'sizes' => json_encode(['M', 'L']),
                'colors' => json_encode([
                    ['name' => 'Black', 'hex' => '#111111'],
                    ['name' => 'Ivory', 'hex' => '#f5f5f4'],
                ]),
                'variants' => json_encode([
                    ['size' => 'M', 'color' => 'Black', 'sku' => 'SKU-HEAD-100-M-BLACK', 'price' => 99, 'sale_price' => 79, 'stock' => 4],
                    ['size' => 'M', 'color' => 'Ivory', 'sku' => 'SKU-HEAD-100-M-IVORY', 'price' => 99, 'sale_price' => 79, 'stock' => 3],
                    ['size' => 'L', 'color' => 'Black', 'sku' => 'SKU-HEAD-100-L-BLACK', 'price' => 109, 'sale_price' => 89, 'stock' => 2],
                    ['size' => 'L', 'color' => 'Ivory', 'sku' => 'SKU-HEAD-100-L-IVORY', 'price' => 109, 'sale_price' => null, 'stock' => 1],
                ]),
                'main_image' => UploadedFile::fake()->image('main.jpg', 800, 800),
                'additional_images' => [
                    UploadedFile::fake()->image('extra.jpg', 800, 800),
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'wireless-headphones')->first();
        $this->assertNotNull($product);
        $this->assertSame('SKU-HEAD-100', $product->sku);
        $this->assertFalse($product->is_unlaunched);
        $this->assertStringEndsWith('.webp', (string) $product->main_image_path);
        $this->assertSame(2, $product->sizes()->count());
        $this->assertSame(2, $product->colors()->count());
        $this->assertSame(4, $product->variants()->count());
        $this->assertSame(10, (int) $product->fresh()->stock);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'SKU-HEAD-100-L-BLACK',
            'stock' => 2,
        ]);
        $this->assertSame(1, $product->images()->count());
        $this->assertStringContainsString('<h2>Features</h2>', (string) $product->description);
        $this->assertStringContainsString('<strong>audio</strong>', (string) $product->description);
    }

    public function test_admin_can_upload_color_specific_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Color Tee',
                'slug' => 'color-tee',
                'sku' => 'SKU-COLOR-TEE',
                'short_description' => 'Soft cotton tee',
                'description' => '<p>Comfort fit</p>',
                'price' => 40,
                'sale_price' => null,
                'stock' => 0,
                'rating' => 4,
                'category_id' => $category->id,
                'is_unlaunched' => false,
                'sort_order' => 1,
                'sizes' => json_encode(['M']),
                'colors' => json_encode([
                    ['name' => 'Black', 'hex' => '#111111'],
                    ['name' => 'Red', 'hex' => '#ef4444'],
                ]),
                'variants' => json_encode([
                    ['size' => 'M', 'color' => 'Black', 'sku' => 'SKU-COLOR-TEE-M-BLACK', 'price' => 40, 'sale_price' => null, 'stock' => 5],
                    ['size' => 'M', 'color' => 'Red', 'sku' => 'SKU-COLOR-TEE-M-RED', 'price' => 45, 'sale_price' => 35, 'stock' => 3],
                ]),
                'main_image' => UploadedFile::fake()->image('main.jpg', 800, 800),
                'additional_images' => [
                    UploadedFile::fake()->image('shared.jpg', 800, 800),
                ],
                'color_images' => [
                    'Black' => [UploadedFile::fake()->image('black.jpg', 800, 800)],
                    'Red' => [UploadedFile::fake()->image('red.jpg', 800, 800)],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'color-tee')->first();
        $this->assertNotNull($product);
        $this->assertSame(3, $product->images()->count());

        $black = $product->colors()->where('name', 'Black')->first();
        $red = $product->colors()->where('name', 'Red')->first();
        $this->assertNotNull($black);
        $this->assertNotNull($red);

        $this->assertSame(1, $product->images()->whereNull('product_color_id')->count());
        $this->assertSame(1, $product->images()->where('product_color_id', $black->id)->count());
        $this->assertSame(1, $product->images()->where('product_color_id', $red->id)->count());

        $this->get(route('products.show', ['product' => 'color-tee']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Product/Pages/Show')
                ->has('product.imagesByColor.Black', 1)
                ->has('product.imagesByColor.Red', 1)
                ->has('product.sharedImages')
                ->where('product.variants.1.effectivePrice', 35)
            );
    }

    public function test_unlaunched_product_is_hidden_on_storefront(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'hidden-item',
            'is_unlaunched' => true,
        ]);

        $this->get(route('products.show', ['product' => 'hidden-item']))
            ->assertNotFound();
    }
}
