<?php

namespace Tests\Feature\Modules\Product;

use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Category\Models\Category;
use App\Modules\Product\Enums\QuantityDiscountType;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductQuantityDiscount;
use App\Modules\Product\Services\ProductQuantityPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductQuantityDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_quantity_offers_on_product_create(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Travel Bag',
                'slug' => 'travel-bag',
                'sku' => 'BAG-100',
                'price' => 250,
                'sale_price' => null,
                'stock' => 20,
                'rating' => 4,
                'category_id' => $category->id,
                'is_unlaunched' => false,
                'sort_order' => 1,
                'sizes' => json_encode([]),
                'colors' => json_encode([]),
                'variants' => json_encode([]),
                'quantity_discounts' => json_encode([
                    ['min_quantity' => 3, 'type' => 'bundle', 'value' => 650],
                    ['min_quantity' => 5, 'type' => 'percent', 'value' => 15],
                ]),
                'main_image' => UploadedFile::fake()->image('bag.jpg', 800, 800),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'travel-bag')->first();
        $this->assertNotNull($product);
        $this->assertSame(2, $product->quantityDiscounts()->count());
        $this->assertDatabaseHas('product_quantity_discounts', [
            'product_id' => $product->id,
            'min_quantity' => 3,
            'type' => 'bundle',
            'value' => 650,
        ]);
    }

    public function test_bundle_offer_prices_three_items_as_pack_total(): void
    {
        $product = Product::factory()->create(['price' => 250, 'sale_price' => null]);
        ProductQuantityDiscount::query()->create([
            'product_id' => $product->id,
            'min_quantity' => 3,
            'type' => QuantityDiscountType::Bundle,
            'value' => 650,
            'sort_order' => 0,
        ]);

        $pricing = app(ProductQuantityPricing::class);

        $one = $pricing->priceLine($product->fresh(['quantityDiscounts']), 250, 1);
        $this->assertSame(250.0, $one['line_total']);
        $this->assertNull($one['quantity_discount']);

        $three = $pricing->priceLine($product->fresh(['quantityDiscounts']), 250, 3);
        $this->assertSame(650.0, $three['line_total']);
        $this->assertSame(100.0, $three['savings']);
        $this->assertSame('bundle', $three['quantity_discount']['type']);

        $four = $pricing->priceLine($product->fresh(['quantityDiscounts']), 250, 4);
        $this->assertSame(900.0, $four['line_total']);
    }

    public function test_cart_applies_quantity_bundle_offer(): void
    {
        $shopper = User::factory()->create(['is_admin' => false]);
        $product = Product::factory()->create([
            'price' => 250,
            'sale_price' => null,
            'stock' => 10,
            'is_unlaunched' => false,
        ]);
        ProductQuantityDiscount::query()->create([
            'product_id' => $product->id,
            'min_quantity' => 3,
            'type' => QuantityDiscountType::Bundle,
            'value' => 650,
            'sort_order' => 0,
        ]);

        CartItem::query()->create([
            'user_id' => $shopper->id,
            'product_id' => $product->id,
            'variant_key' => '0',
            'quantity' => 3,
        ]);

        $this->actingAs($shopper)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cart/Pages/Index')
                ->where('cart.items.0.line_total', 650)
                ->where('cart.items.0.quantity_discount.type', 'bundle')
                ->where('cart.items.0.quantity_savings', 100)
            );
    }
}
