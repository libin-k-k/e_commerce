<?php

namespace Tests\Feature\Modules\Admin;

use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Coupon\Enums\CouponType;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Models\CouponRedemption;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CouponAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_percent_coupon_with_max_discount(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.coupons.store'), [
                'code' => 'SAVE20',
                'name' => 'Save twenty',
                'type' => 'percent',
                'value' => 20,
                'max_discount_amount' => 100,
                'min_order_amount' => 500,
                'max_order_amount' => 5000,
                'has_validity' => false,
                'total_usage_limit' => 5,
                'per_user_usage_limit' => 2,
                'first_order_only' => true,
                'is_active' => true,
                'product_ids' => [$product->id],
            ])
            ->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::query()->where('code', 'SAVE20')->first();
        $this->assertNotNull($coupon);
        $this->assertSame(CouponType::Percent, $coupon->type);
        $this->assertSame(100.0, (float) $coupon->max_discount_amount);
        $this->assertTrue($coupon->first_order_only);
        $this->assertTrue($coupon->products()->whereKey($product->id)->exists());
    }

    public function test_percent_coupon_requires_max_discount_amount(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.coupons.create'))
            ->post(route('admin.coupons.store'), [
                'code' => 'BAD20',
                'type' => 'percent',
                'value' => 20,
                'has_validity' => false,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.coupons.create'))
            ->assertSessionHasErrors('max_discount_amount');
    }

    public function test_cart_applies_percent_coupon_capped_by_max_discount(): void
    {
        $shopper = User::factory()->create(['is_admin' => false]);
        $product = Product::factory()->create([
            'price' => 1000,
            'sale_price' => null,
            'stock' => 5,
            'is_unlaunched' => false,
        ]);

        Coupon::query()->create([
            'code' => 'CAP20',
            'type' => CouponType::Percent,
            'value' => 20,
            'max_discount_amount' => 100,
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
            ->post(route('cart.coupon.store'), ['code' => 'CAP20'])
            ->assertRedirect();

        $this->actingAs($shopper)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cart/Pages/Index')
                ->where('cart.coupon.code', 'CAP20')
                ->where('cart.coupon.discount', 100)
            );
    }

    public function test_total_usage_limit_blocks_coupon(): void
    {
        $shopper = User::factory()->create(['is_admin' => false]);
        $product = Product::factory()->create([
            'price' => 200,
            'sale_price' => null,
            'is_unlaunched' => false,
        ]);

        $coupon = Coupon::query()->create([
            'code' => 'ONCE5',
            'type' => CouponType::Fixed,
            'value' => 50,
            'total_usage_limit' => 1,
            'is_active' => true,
        ]);

        CouponRedemption::query()->create([
            'coupon_id' => $coupon->id,
            'user_id' => User::factory()->create()->id,
            'discount_amount' => 50,
            'status' => 'completed',
        ]);

        CartItem::query()->create([
            'user_id' => $shopper->id,
            'product_id' => $product->id,
            'variant_key' => '0',
            'quantity' => 1,
        ]);

        $this->actingAs($shopper)
            ->from(route('cart.index'))
            ->post(route('cart.coupon.store'), ['code' => 'ONCE5'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('code');
    }

    public function test_admin_coupons_index_renders(): void
    {
        $admin = User::factory()->admin()->create();
        Coupon::query()->create([
            'code' => 'LIST1',
            'type' => CouponType::Fixed,
            'value' => 25,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Pages/Coupons/Index')
                ->has('coupons', 1)
                ->where('coupons.0.code', 'LIST1')
            );
    }
}
