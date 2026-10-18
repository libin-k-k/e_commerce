<?php

namespace Tests\Feature\Modules\Cart;

use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Coupon\Enums\CouponType;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Services\CouponCartService;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_shares_eligible_coupons_only(): void
    {
        $shopper = User::factory()->create(['is_admin' => false]);
        $product = Product::factory()->create([
            'price' => 500,
            'sale_price' => null,
            'is_unlaunched' => false,
        ]);
        $otherProduct = Product::factory()->create([
            'price' => 400,
            'sale_price' => null,
            'is_unlaunched' => false,
        ]);

        Coupon::query()->create([
            'code' => 'SAVE10',
            'name' => 'Save ten',
            'type' => CouponType::Percent,
            'value' => 10,
            'max_discount_amount' => 80,
            'is_active' => true,
            'first_order_only' => false,
        ]);

        Coupon::query()->create([
            'code' => 'HIGHMIN',
            'name' => 'Needs big cart',
            'type' => CouponType::Fixed,
            'value' => 50,
            'min_order_amount' => 5000,
            'is_active' => true,
            'first_order_only' => false,
        ]);

        $productOnly = Coupon::query()->create([
            'code' => 'PICKONLY',
            'name' => 'Other product only',
            'type' => CouponType::Percent,
            'value' => 15,
            'max_discount_amount' => 100,
            'is_active' => true,
            'first_order_only' => false,
        ]);
        $productOnly->products()->sync([$otherProduct->id]);

        CartItem::query()->create([
            'user_id' => $shopper->id,
            'product_id' => $product->id,
            'variant_key' => '0',
            'quantity' => 1,
        ]);

        $this->actingAs($shopper)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart/Pages/Index')
                ->has('cart.availableCoupons', 1)
                ->where('cart.availableCoupons.0.code', 'SAVE10')
                ->where('cart.availableCoupons.0.applicable', true)
                ->where('cart.availableCoupons.0.headline', '10% off')
            );
    }

    public function test_applying_coupon_sets_celebration_flash_and_replaces_existing(): void
    {
        $shopper = User::factory()->create(['is_admin' => false]);
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => null,
            'is_unlaunched' => false,
        ]);

        Coupon::query()->create([
            'code' => 'FIRST15',
            'type' => CouponType::Percent,
            'value' => 15,
            'max_discount_amount' => 200,
            'is_active' => true,
            'first_order_only' => false,
        ]);

        Coupon::query()->create([
            'code' => 'NEXT50',
            'type' => CouponType::Fixed,
            'value' => 50,
            'is_active' => true,
            'first_order_only' => false,
        ]);

        CartItem::query()->create([
            'user_id' => $shopper->id,
            'product_id' => $product->id,
            'variant_key' => '0',
            'quantity' => 1,
        ]);

        $this->actingAs($shopper)
            ->from(route('cart.index'))
            ->post(route('cart.coupon.store'), ['code' => 'FIRST15'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('coupon_celebration', true)
            ->assertSessionHas(CouponCartService::SessionKey, 'FIRST15');

        $this->actingAs($shopper)
            ->from(route('cart.index'))
            ->post(route('cart.coupon.store'), ['code' => 'NEXT50'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('coupon_celebration', true)
            ->assertSessionHas(CouponCartService::SessionKey, 'NEXT50');

        $this->actingAs($shopper)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cart.coupon.code', 'NEXT50')
                ->where('cart.coupon.discount', 50)
                ->where('cart.availableCoupons.0.code', 'NEXT50')
                ->where('cart.availableCoupons.0.applied', true)
                ->where('cart.availableCoupons.1.code', 'FIRST15')
                ->where('cart.availableCoupons.1.applied', false)
            );
    }
}
