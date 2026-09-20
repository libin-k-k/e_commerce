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
        return [
            [
                'id' => 'popular',
                'name' => 'Popular',
                'tone' => 'popular',
                'featuredTitle' => 'Featured picks',
                'featured' => [
                    [
                        'name' => 'Western Wear',
                        'slug' => 'western-wear',
                        'tone' => 'western',
                        'image' => $this->image('western_wear.png'),
                    ],
                    [
                        'name' => 'Beauty',
                        'slug' => 'beauty',
                        'tone' => 'beauty',
                        'image' => $this->image('beauty.png'),
                    ],
                ],
                'sectionTitle' => 'All Popular',
                'children' => [
                    [
                        'name' => 'Bags & Accessories',
                        'slug' => 'bags',
                        'tone' => 'bags',
                        'image' => $this->image('bags_and_accessories.png'),
                    ],
                    [
                        'name' => 'Footwear',
                        'slug' => 'footwear',
                        'tone' => 'footwear',
                        'image' => $this->image('footwear.png'),
                    ],
                    [
                        'name' => 'Western Wear',
                        'slug' => 'western-wear',
                        'tone' => 'western',
                        'image' => $this->image('western_wear.png'),
                    ],
                    [
                        'name' => 'Kids Wear',
                        'slug' => 'kids-wear',
                        'tone' => 'kids',
                        'image' => $this->image('kids_wear.png'),
                    ],
                    [
                        'name' => 'Home Decor',
                        'slug' => 'home-decor',
                        'tone' => 'home',
                        'image' => $this->image('home_decor.png'),
                    ],
                    [
                        'name' => 'Beauty',
                        'slug' => 'beauty',
                        'tone' => 'beauty',
                        'image' => $this->image('beauty.png'),
                    ],
                ],
            ],
        ];
    }

    private function image(string $filename): string
    {
        return (string) PublicUrl::for('assets/website/category/'.$filename);
    }
}
