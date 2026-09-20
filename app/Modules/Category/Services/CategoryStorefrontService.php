<?php

namespace App\Modules\Category\Services;

use App\Modules\Category\Models\Category;
use App\Modules\Category\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryStorefrontService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    /**
     * Menu shape for the storefront category drawer.
     *
     * @return list<array<string, mixed>>
     */
    public function menu(): array
    {
        return $this->categories
            ->activeMainsWithChildren()
            ->map(function (Category $main): array {
                $children = $main->children
                    ->map(fn (Category $child): array => [
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'image' => $child->imageUrl() ?? $main->imageUrl(),
                        'href' => '/products?category='.$child->slug,
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => $main->slug,
                    'name' => $main->name,
                    'tone' => $main->slug,
                    'featuredTitle' => 'Featured',
                    'featured' => array_slice($children, 0, 2),
                    'sectionTitle' => 'All '.$main->name,
                    'children' => $children,
                    'image' => $main->imageUrl(),
                    'href' => '/products?category='.$main->slug,
                ];
            })
            ->values()
            ->all();
    }
}
