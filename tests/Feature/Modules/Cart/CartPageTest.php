<?php

namespace Tests\Feature\Modules\Cart;

use App\Models\User;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Wishlist\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    private User $shopper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopper = User::factory()->create(['is_admin' => false]);
    }

    public function test_cart_page_renders_lines_delivery_rule_and_recommendations(): void
    {
        config(['commerce.delivery.free_above' => 499, 'commerce.delivery.fee' => 99]);

        $category = Category::factory()->create(['name' => 'Footwear']);
        $inCart = Product::factory()->for($category)->create([
            'name' => 'Casual Sneakers',
            'price' => 200,
            'sale_price' => 150,
            'stock' => 5,
        ]);
        $other = Product::factory()->create(['name' => 'Leather Handbag']);
        $this->addToCart($inCart, 2);

        $response = $this->actingAs($this->shopper)->get(route('cart.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Cart/Pages/Index')
            ->where('seo.title', 'Your cart')
            ->where('delivery.freeAbove', 499)
            ->where('delivery.fee', 99)
            ->has('recommendations', 1)
            ->where('recommendations.0.id', $other->id)
            ->has('cart.items', 1)
            ->where('cart.items.0.name', 'Casual Sneakers')
            ->where('cart.items.0.category', 'Footwear')
            ->where('cart.items.0.unit_price', 150)
            ->where('cart.items.0.unit_mrp', 200)
            ->where('cart.items.0.line_total', 300)
            ->where('cart.items.0.line_mrp', 400)
            ->where('cart.items.0.discount_percent', 25)
            ->where('cart.items.0.max_quantity', 5)
        );
    }

    public function test_selected_lines_can_be_removed_without_touching_other_carts(): void
    {
        $first = $this->addToCart(Product::factory()->create());
        $second = $this->addToCart(Product::factory()->create());
        $stranger = CartItem::query()->create([
            'user_id' => User::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'variant_key' => '0',
            'quantity' => 1,
        ]);

        $this->actingAs($this->shopper)
            ->from(route('cart.index'))
            ->delete(route('cart.destroy-many'), ['items' => [$first->id, $stranger->id]])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', '1 item removed from cart.');

        $this->assertModelMissing($first);
        $this->assertModelExists($second);
        $this->assertModelExists($stranger);
    }

    public function test_selected_lines_move_to_the_wishlist(): void
    {
        $alreadyWished = Product::factory()->create();
        $fresh = Product::factory()->create();
        WishlistItem::query()->create(['user_id' => $this->shopper->id, 'product_id' => $alreadyWished->id]);
        $lines = [$this->addToCart($alreadyWished), $this->addToCart($fresh)];

        $this->actingAs($this->shopper)
            ->from(route('cart.index'))
            ->post(route('cart.move-to-wishlist'), ['items' => array_map(fn (CartItem $line): int => $line->id, $lines)])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', '2 items moved to wishlist.');

        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('wishlist_items', 2);
        $this->assertDatabaseHas('wishlist_items', ['user_id' => $this->shopper->id, 'product_id' => $fresh->id]);
    }

    public function test_bulk_actions_require_item_ids(): void
    {
        $this->actingAs($this->shopper)
            ->delete(route('cart.destroy-many'), ['items' => []])
            ->assertSessionHasErrors('items');

        $this->actingAs($this->shopper)
            ->post(route('cart.move-to-wishlist'), ['items' => ['abc']])
            ->assertSessionHasErrors('items.0');
    }

    private function addToCart(Product $product, int $quantity = 1): CartItem
    {
        return CartItem::query()->create([
            'user_id' => $this->shopper->id,
            'product_id' => $product->id,
            'variant_key' => '0',
            'quantity' => $quantity,
        ]);
    }
}
