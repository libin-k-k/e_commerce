<?php

namespace App\Modules\Product\Repositories\Contracts;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Enums\PriceRange;
use App\Modules\Product\Enums\ProductSort;

interface ProductRepositoryInterface
{
    /**
     * Launched products for the storefront listing.
     *
     * @param  array{q: ?string, size: ?string, color: ?string, sort: ProductSort, price: ?PriceRange, minPrice: ?int, maxPrice: ?int, rating: ?int}  $filters
     * @return list<array<string, mixed>>
     */
    public function search(array $filters, ?Category $category = null): array;

    /**
     * Distinct size names offered by launched products in the category.
     *
     * @return list<string>
     */
    public function sizeNames(?Category $category = null): array;

    /**
     * Launched product counts, keyed by main and sub category id.
     *
     * @return array{total: int, byCategory: array<int, int>}
     */
    public function launchedCounts(): array;

    /**
     * Launched products in the category rated at or above each value.
     *
     * @param  list<int>  $ratings
     * @return array<int, int>
     */
    public function ratingCounts(array $ratings, ?Category $category = null): array;

    /**
     * Distinct colours offered by launched products in the category.
     *
     * @return list<array{name: string, hex: ?string}>
     */
    public function colorOptions(?Category $category = null): array;

    /**
     * Highest list price in the category, rounded up to the next hundred.
     */
    public function priceCeiling(?Category $category = null): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function featured(int $limit = 4): array;

    /**
     * Products currently on sale (base or variant sale price).
     *
     * @return list<array<string, mixed>>
     */
    public function onSale(): array;

    /**
     * Launched products whose name, short description or SKU contains the term,
     * or every launched product in storefront order when the term is null.
     *
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function suggest(?string $term, int $limit): array;

    /**
     * Launched products in storefront order, skipping the given product ids.
     *
     * @param  list<int>  $excludeIds
     * @return list<array<string, mixed>>
     */
    public function recommended(array $excludeIds, int $limit): array;

    /**
     * Products from the same main category first, topped up with other launched products.
     *
     * @return list<array<string, mixed>>
     */
    public function related(string $slug, int $limit = 4): array;
}
