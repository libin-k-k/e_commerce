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
                return [
                    'id' => $main->slug,
                    'name' => $main->name,
                    'image' => $main->imageUrl(),
                    'href' => '/products?category='.$main->slug,
                    'children' => $main->children
                        ->map(fn (Category $child): array => [
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'image' => $child->imageUrl() ?? $main->imageUrl(),
                            'href' => '/products?category='.$child->slug,
                            'itemCount' => (int) ($child->launched_products_count ?? 0),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }
}
