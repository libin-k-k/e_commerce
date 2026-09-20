<?php

namespace App\Modules\Category\Database\Seeders;

use App\Modules\Category\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Western Wear',
                'slug' => 'western-wear',
                'image_path' => 'assets/website/category/western_wear.png',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Tops', 'slug' => 'western-tops', 'sort_order' => 1],
                    ['name' => 'Dresses', 'slug' => 'western-dresses', 'sort_order' => 2],
                    ['name' => 'Jeans', 'slug' => 'western-jeans', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Beauty',
                'slug' => 'beauty',
                'image_path' => 'assets/website/category/beauty.png',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Makeup', 'slug' => 'beauty-makeup', 'sort_order' => 1],
                    ['name' => 'Skincare', 'slug' => 'beauty-skincare', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Kids Wear',
                'slug' => 'kids-wear',
                'image_path' => 'assets/website/category/kids_wear.png',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Boys', 'slug' => 'kids-boys', 'sort_order' => 1],
                    ['name' => 'Girls', 'slug' => 'kids-girls', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Home Decor',
                'slug' => 'home-decor',
                'image_path' => 'assets/website/category/home_decor.png',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Lighting', 'slug' => 'home-lighting', 'sort_order' => 1],
                    ['name' => 'Decor', 'slug' => 'home-accents', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Footwear',
                'slug' => 'footwear',
                'image_path' => 'assets/website/category/footwear.png',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Sneakers', 'slug' => 'footwear-sneakers', 'sort_order' => 1],
                    ['name' => 'Sandals', 'slug' => 'footwear-sandals', 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Bags & Accessories',
                'slug' => 'bags',
                'image_path' => 'assets/website/category/bags_and_accessories.png',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Handbags', 'slug' => 'bags-handbags', 'sort_order' => 1],
                    ['name' => 'Wallets', 'slug' => 'bags-wallets', 'sort_order' => 2],
                ],
            ],
        ];

        foreach ($tree as $main) {
            $children = $main['children'];
            unset($main['children']);

            $parent = Category::query()->updateOrCreate(
                ['slug' => $main['slug']],
                [
                    ...$main,
                    'parent_id' => null,
                    'is_active' => true,
                    'description' => null,
                ],
            );

            foreach ($children as $child) {
                Category::query()->updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        ...$child,
                        'parent_id' => $parent->id,
                        'image_path' => $main['image_path'],
                        'is_active' => true,
                        'description' => null,
                    ],
                );
            }
        }
    }
}
