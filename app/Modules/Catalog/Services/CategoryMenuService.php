<?php

namespace App\Modules\Catalog\Services;

use App\Core\Media\PublicUrl;
use App\Modules\Category\Services\CategoryStorefrontService;

class CategoryMenuService
{
    public function __construct(
        private readonly CategoryStorefrontService $categoryStorefrontService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function menu(): array
    {
        $menu = $this->categoryStorefrontService->menu();

        if ($menu !== []) {
            return $menu;
        }

        return $this->fallbackMenu();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fallbackMenu(): array
    {
        $children = [
            ['name' => 'Bags & Accessories', 'slug' => 'bags', 'image' => 'bags_and_accessories.png'],
            ['name' => 'Footwear', 'slug' => 'footwear', 'image' => 'footwear.png'],
            ['name' => 'Western Wear', 'slug' => 'western-wear', 'image' => 'western_wear.png'],
            ['name' => 'Kids Wear', 'slug' => 'kids-wear', 'image' => 'kids_wear.png'],
            ['name' => 'Home Decor', 'slug' => 'home-decor', 'image' => 'home_decor.png'],
            ['name' => 'Beauty', 'slug' => 'beauty', 'image' => 'beauty.png'],
        ];

        return [
            [
                'id' => 'popular',
                'name' => 'Popular',
                'image' => null,
                'href' => '/products',
                'children' => array_map(fn (array $child): array => [
                    'name' => $child['name'],
                    'slug' => $child['slug'],
                    'image' => $this->image($child['image']),
                    'href' => '/products?category='.$child['slug'],
                    'itemCount' => 0,
                ], $children),
            ],
        ];
    }

    private function image(string $filename): string
    {
        return (string) PublicUrl::for('assets/website/category/'.$filename);
    }
}
