<?php

namespace App\Modules\Category\Services;

use App\Modules\Category\Models\Category;
use App\Modules\Category\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryStorefrontService
{
    private const Tones = ['fashion', 'beauty', 'kids', 'home', 'footwear', 'all'];

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

    /**
     * Horizontal quick-category strip on the home page.
     *
     * @return list<array<string, mixed>>
     */
    public function quickCategories(): array
    {
        return $this->categories
            ->activeMainsWithChildren()
            ->values()
            ->map(function (Category $category, int $index): array {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'short' => mb_substr($category->name, 0, 2),
                    'tone' => self::Tones[$index % count(self::Tones)],
                    'href' => '/products?category='.$category->slug,
                    'image' => $category->imageUrl(),
                ];
            })
            ->all();
    }

    /**
     * Offer-zone category tiles on the home page.
     *
     * @return list<array<string, mixed>>
     */
    public function offerHighlights(): array
    {
        return $this->categories
            ->activeMainsWithChildren()
            ->values()
            ->map(function (Category $category, int $index): array {
                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'badge' => null,
                    'tone' => self::Tones[$index % count(self::Tones)],
                    'image' => $category->imageUrl(),
                ];
            })
            ->all();
    }
}
