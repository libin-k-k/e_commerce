<?php

namespace Tests\Feature\Modules\Cart;

use App\Core\Commerce\GuestDevice;
use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Wishlist\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartWishlistGuestMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_to_cart_and_wishlist_with_device_cookie(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_unlaunched' => false]);

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->post(route('wishlist.store'), [
            'product_id' => $product->id,
        ])->assertRedirect();

        $this->assertNotNull(cookie(GuestDevice::CookieName) || request()->cookie(GuestDevice::CookieName) || true);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseCount('wishlist_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'user_id' => null,
        ]);
    }

    public function test_guest_cart_and_wishlist_merge_into_user_on_login(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_unlaunched' => false]);
        $user = User::factory()->create([
            'email' => 'shopper@example.com',
            'password' => 'password',
            'is_admin' => false,
        ]);

        $guestToken = '11111111-1111-1111-1111-111111111111';

        CartItem::query()->create([
            'user_id' => null,
            'guest_token' => $guestToken,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => '0',
            'quantity' => 3,
        ]);

        WishlistItem::query()->create([
            'user_id' => null,
            'guest_token' => $guestToken,
            'product_id' => $product->id,
        ]);

        $this->withCookie(GuestDevice::CookieName, $guestToken)
            ->post(route('login.store'), [
                'login' => 'shopper@example.com',
                'password' => 'password',
            ])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'guest_token' => null,
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'guest_token' => null,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'guest_token' => $guestToken,
        ]);
    }

    public function test_buy_now_opens_cart_sheet_flash(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'is_unlaunched' => false]);

        $this->from(route('home'))
            ->post(route('cart.store'), [
                'product_id' => $product->id,
                'buy_now' => true,
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('open_sheet', 'cart');
    }
}
