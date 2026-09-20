<?php

namespace Database\Factories;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'subcategory_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'short_description' => fake()->sentence(),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'price' => fake()->randomFloat(2, 10, 200),
            'sale_price' => null,
            'stock' => fake()->numberBetween(0, 50),
            'rating' => fake()->randomFloat(1, 3, 5),
            'main_image_path' => 'assets/website/products/wireless_headphone_1.png',
            'is_unlaunched' => false,
            'sort_order' => 0,
        ];
    }

    public function unlaunched(): static
    {
        return $this->state(fn (): array => [
            'is_unlaunched' => true,
        ]);
    }
}
