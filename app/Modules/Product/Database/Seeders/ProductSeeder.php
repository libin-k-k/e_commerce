<?php

namespace App\Modules\Product\Database\Seeders;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $western = Category::query()->where('slug', 'western-wear')->first();
        $home = Category::query()->where('slug', 'home-decor')->first();
        $bags = Category::query()->where('slug', 'bags')->first();

        $westernTops = Category::query()->where('slug', 'western-tops')->first();
        $lighting = Category::query()->where('slug', 'home-lighting')->first();
        $handbags = Category::query()->where('slug', 'bags-handbags')->first();

        if ($western === null || $home === null || $bags === null) {
            return;
        }

        $products = [
            [
                'name' => 'Wireless headphones',
                'slug' => 'wireless-headphones',
                'sku' => 'SKU-HEAD-001',
                'category_id' => $western->id,
                'subcategory_id' => $westernTops?->id,
                'short_description' => 'Clear sound, all-day comfort, and wireless freedom for work and travel.',
                'description' => '<p>Enjoy rich audio with deep bass and crisp highs. Soft ear cushions and a lightweight frame make long sessions comfortable.</p>',
                'price' => 99.00,
                'sale_price' => 79.00,
                'stock' => 24,
                'rating' => 4.6,
                'main_image_path' => 'assets/website/products/wireless_headphone_1.png',
                'gallery' => [
                    'assets/website/products/wireless_headphone_1.png',
                    'assets/website/products/wireless_headphone_2.png',
                    'assets/website/products/wireless_headphone_3.png',
                ],
                'sizes' => [],
                'colors' => [
                    ['name' => 'Black', 'hex' => '#111827'],
                    ['name' => 'Ivory', 'hex' => '#f5f5f4'],
                    ['name' => 'Blue', 'hex' => '#2563eb'],
                ],
                'variant_prices' => [
                    'Black' => ['price' => 99, 'sale_price' => 79, 'stock' => 10],
                    'Ivory' => ['price' => 99, 'sale_price' => 79, 'stock' => 8],
                    'Blue' => ['price' => 109, 'sale_price' => 89, 'stock' => 6],
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Minimal desk lamp',
                'slug' => 'minimal-desk-lamp',
                'sku' => 'SKU-LAMP-001',
                'category_id' => $home->id,
                'subcategory_id' => $lighting?->id,
                'short_description' => 'Soft adjustable light for focused work and calm evenings.',
                'description' => '<p>A clean desk lamp with warm dimmable light. Stable base and compact footprint for small desks.</p>',
                'price' => 45.00,
                'sale_price' => null,
                'stock' => 18,
                'rating' => 4.4,
                'main_image_path' => 'assets/website/products/minimal_desk_lamp_1.png',
                'gallery' => [
                    'assets/website/products/minimal_desk_lamp_1.png',
                    'assets/website/products/minimal_desk_lamp_2.png',
                    'assets/website/products/minimal_desk_lamp_3.png',
                ],
                'sizes' => [],
                'colors' => [
                    ['name' => 'Matte Black', 'hex' => '#1f2937'],
                    ['name' => 'Warm White', 'hex' => '#f8fafc'],
                ],
                'variant_prices' => [
                    'Matte Black' => ['price' => 45, 'sale_price' => null, 'stock' => 10],
                    'Warm White' => ['price' => 49, 'sale_price' => null, 'stock' => 8],
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Everyday tote bag',
                'slug' => 'everyday-tote-bag',
                'sku' => 'SKU-TOTE-001',
                'category_id' => $bags->id,
                'subcategory_id' => $handbags?->id,
                'short_description' => 'Spacious tote for work days, weekends, and light travel.',
                'description' => '<p>Durable canvas tote with inner pocket and reinforced handles.</p>',
                'price' => 39.00,
                'sale_price' => 32.00,
                'stock' => 40,
                'rating' => 4.5,
                'main_image_path' => 'assets/website/products/everyday_tote_bag_1.png',
                'gallery' => [
                    'assets/website/products/everyday_tote_bag_1.png',
                    'assets/website/products/everyday_tote_bag_2.png',
                    'assets/website/products/everyday_tote_bag_3.png',
                ],
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'Sand', 'hex' => '#d6d3d1'],
                    ['name' => 'Olive', 'hex' => '#4d7c0f'],
                ],
                'variant_prices' => [
                    'One Size|Sand' => ['price' => 39, 'sale_price' => 32, 'stock' => 22],
                    'One Size|Olive' => ['price' => 42, 'sale_price' => 35, 'stock' => 18],
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Ceramic mug set',
                'slug' => 'ceramic-mug-set',
                'sku' => 'SKU-MUG-001',
                'category_id' => $home->id,
                'subcategory_id' => null,
                'short_description' => 'A set of everyday ceramic mugs with a soft matte finish.',
                'description' => '<p>Microwave-safe ceramic mugs for coffee, tea, and desk days.</p>',
                'price' => 28.00,
                'sale_price' => null,
                'stock' => 55,
                'rating' => 4.3,
                'main_image_path' => 'assets/website/products/ceramic_mug_set_1.png',
                'gallery' => [
                    'assets/website/products/ceramic_mug_set_1.png',
                    'assets/website/products/ceramic_mug_set_2.png',
                    'assets/website/products/ceramic_mug_set_3.png',
                ],
                'sizes' => [],
                'colors' => [
                    ['name' => 'Cream', 'hex' => '#fafaf9'],
                    ['name' => 'Clay', 'hex' => '#c2410c'],
                ],
                'variant_prices' => [
                    'Cream' => ['price' => 28, 'sale_price' => null, 'stock' => 30],
                    'Clay' => ['price' => 30, 'sale_price' => null, 'stock' => 25],
                ],
                'sort_order' => 4,
            ],
        ];

        foreach ($products as $item) {
            $gallery = $item['gallery'];
            $sizes = $item['sizes'];
            $colors = $item['colors'];
            $variantPrices = $item['variant_prices'] ?? [];
            unset($item['gallery'], $item['sizes'], $item['colors'], $item['variant_prices']);

            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    ...$item,
                    'is_unlaunched' => false,
                ],
            );

            $product->variants()->delete();
            $product->sizes()->delete();
            $product->colors()->delete();
            $product->images()->delete();

            foreach ($gallery as $index => $path) {
                $product->images()->create([
                    'path' => $path,
                    'sort_order' => $index,
                ]);
            }

            $sizeMap = [];
            foreach (array_values($sizes) as $index => $sizeName) {
                $sizeMap[$sizeName] = $product->sizes()->create([
                    'name' => $sizeName,
                    'sort_order' => $index,
                ]);
            }

            $colorMap = [];
            foreach (array_values($colors) as $index => $color) {
                $colorMap[$color['name']] = $product->colors()->create([
                    'name' => $color['name'],
                    'hex' => $color['hex'],
                    'sort_order' => $index,
                ]);
            }

            $combos = $this->combinations(array_keys($sizeMap), array_keys($colorMap));
            $totalStock = 0;

            foreach ($combos as $index => $combo) {
                $lookup = $combo['size'] && $combo['color']
                    ? $combo['size'].'|'.$combo['color']
                    : ($combo['size'] ?? $combo['color']);
                $pricing = $variantPrices[$lookup] ?? [
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'stock' => 5,
                ];

                $sizeId = $combo['size'] ? $sizeMap[$combo['size']]->id : null;
                $colorId = $combo['color'] ? $colorMap[$combo['color']]->id : null;
                $suffix = collect([$combo['size'], $combo['color']])
                    ->filter()
                    ->map(fn (string $part): string => Str::upper(Str::slug($part, '')))
                    ->implode('-');

                $product->variants()->create([
                    'product_size_id' => $sizeId,
                    'product_color_id' => $colorId,
                    'combo_key' => ProductVariant::makeComboKey($sizeId, $colorId),
                    'sku' => $item['sku'].'-'.$suffix,
                    'price' => $pricing['price'],
                    'sale_price' => $pricing['sale_price'],
                    'stock' => $pricing['stock'],
                    'sort_order' => $index,
                ]);

                $totalStock += (int) $pricing['stock'];
            }

            $product->update(['stock' => $totalStock]);
        }
    }

    /**
     * @param  list<string>  $sizes
     * @param  list<string>  $colors
     * @return list<array{size: ?string, color: ?string}>
     */
    private function combinations(array $sizes, array $colors): array
    {
        if ($sizes !== [] && $colors !== []) {
            $rows = [];
            foreach ($sizes as $size) {
                foreach ($colors as $color) {
                    $rows[] = ['size' => $size, 'color' => $color];
                }
            }

            return $rows;
        }

        if ($sizes !== []) {
            return array_map(fn (string $size): array => ['size' => $size, 'color' => null], $sizes);
        }

        return array_map(fn (string $color): array => ['size' => null, 'color' => $color], $colors);
    }
}
