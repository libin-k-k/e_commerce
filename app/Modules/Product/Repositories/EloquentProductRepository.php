<?php

namespace App\Modules\Product\Repositories;

use App\Modules\Category\Models\Category;
use App\Modules\Product\Enums\ProductSort;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductColor;
use App\Modules\Product\Models\ProductSize;
use App\Modules\Product\Models\ProductVariant;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function search(array $filters, ?Category $category = null): array
    {
        $sort = $filters['sort'];

        $products = $this->launchedQuery()
            ->when($category !== null, fn (Builder $query) => $this->scopeToCategory($query, $category))
            ->when($filters['q'] !== null, fn (Builder $query) => $this->matchTerm($query, (string) $filters['q']))
            ->when($filters['size'] !== null, fn (Builder $query) => $query
                ->whereHas('sizes', fn (Builder $sizes) => $sizes->where('name', $filters['size'])))
            ->when($filters['color'] !== null, fn (Builder $query) => $query
                ->whereHas('colors', fn (Builder $colors) => $colors->where('name', $filters['color'])))
            ->when($filters['rating'] !== null, fn (Builder $query) => $query->where('rating', '>=', $filters['rating']))
            ->when($sort === ProductSort::Newest, fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($sort === ProductSort::TopRated, fn (Builder $query) => $query->orderByDesc('rating'))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray());

        if ($filters['price'] !== null) {
            $products = $products->filter(
                fn (array $product): bool => $filters['price']->contains((float) $product['priceValue']),
            );
        }

        if ($filters['minPrice'] !== null || $filters['maxPrice'] !== null) {
            $products = $products->filter(function (array $product) use ($filters): bool {
                $price = (float) $product['priceValue'];

                return ($filters['minPrice'] === null || $price >= $filters['minPrice'])
                    && ($filters['maxPrice'] === null || $price <= $filters['maxPrice']);
            });
        }

        if ($sort === ProductSort::PriceLowToHigh) {
            $products = $products->sortBy('priceValue');
        }

        if ($sort === ProductSort::PriceHighToLow) {
            $products = $products->sortByDesc('priceValue');
        }

        return $products->values()->all();
    }

    public function sizeNames(?Category $category = null): array
    {
        return ProductSize::query()
            ->whereHas('product', fn (Builder $query) => $query
                ->where('is_unlaunched', false)
                ->when($category !== null, fn (Builder $scoped) => $this->scopeToCategory($scoped, $category)))
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    public function launchedCounts(): array
    {
        $rows = Product::query()
            ->where('is_unlaunched', false)
            ->selectRaw('category_id, subcategory_id, count(*) as aggregate')
            ->groupBy('category_id', 'subcategory_id')
            ->get();

        $byCategory = [];

        foreach ($rows as $row) {
            $count = (int) $row->aggregate;
            $byCategory[$row->category_id] = ($byCategory[$row->category_id] ?? 0) + $count;

            if ($row->subcategory_id !== null) {
                $byCategory[$row->subcategory_id] = ($byCategory[$row->subcategory_id] ?? 0) + $count;
            }
        }

        return [
            'total' => (int) $rows->sum('aggregate'),
            'byCategory' => $byCategory,
        ];
    }

    public function ratingCounts(array $ratings, ?Category $category = null): array
    {
        $counts = [];

        foreach ($ratings as $rating) {
            $counts[$rating] = Product::query()
                ->where('is_unlaunched', false)
                ->when($category !== null, fn (Builder $query) => $this->scopeToCategory($query, $category))
                ->where('rating', '>=', $rating)
                ->count();
        }

        return $counts;
    }

    public function colorOptions(?Category $category = null): array
    {
        return ProductColor::query()
            ->whereHas('product', fn (Builder $query) => $query
                ->where('is_unlaunched', false)
                ->when($category !== null, fn (Builder $scoped) => $this->scopeToCategory($scoped, $category)))
            ->orderBy('name')
            ->get(['name', 'hex'])
            ->unique(fn (ProductColor $color): string => mb_strtolower($color->name))
            ->map(fn (ProductColor $color): array => ['name' => $color->name, 'hex' => $color->hex])
            ->values()
            ->all();
    }

    public function priceCeiling(?Category $category = null): int
    {
        $scope = fn (Builder $query) => $query
            ->where('is_unlaunched', false)
            ->when($category !== null, fn (Builder $scoped) => $this->scopeToCategory($scoped, $category));

        $highest = max(
            (float) $scope(Product::query())->max('price'),
            (float) ProductVariant::query()->whereHas('product', $scope)->max('price'),
        );

        return max(100, (int) (ceil($highest / 100) * 100));
    }

    public function findBySlug(string $slug): ?array
    {
        $product = $this->launchedQuery()
            ->where('slug', $slug)
            ->first();

        return $product?->toStorefrontArray();
    }

    public function recommended(array $excludeIds, int $limit): array
    {
        return $this->launchedQuery()
            ->whereKeyNot($excludeIds)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->values()
            ->all();
    }

    public function featured(int $limit = 4): array
    {
        return $this->launchedQuery()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->values()
            ->all();
    }

    public function onSale(): array
    {
        return $this->launchedQuery()
            ->where(function ($query): void {
                $query->where(function ($base): void {
                    $base->whereNotNull('sale_price')
                        ->where('sale_price', '>', 0)
                        ->whereColumn('sale_price', '<', 'price');
                })->orWhereHas('variants', function ($variants): void {
                    $variants->whereNotNull('sale_price')
                        ->where('sale_price', '>', 0)
                        ->whereColumn('sale_price', '<', 'price');
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->filter(fn (array $product): bool => filled($product['compareAtPrice'] ?? null))
            ->values()
            ->all();
    }

    public function related(string $slug, int $limit = 4): array
    {
        $current = Product::query()->where('slug', $slug)->first();

        $sameCategory = $current === null
            ? collect()
            : $this->launchedQuery()
                ->where('id', '!=', $current->id)
                ->where('category_id', $current->category_id)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

        $others = $sameCategory->count() >= $limit
            ? collect()
            : $this->launchedQuery()
                ->where('slug', '!=', $slug)
                ->whereNotIn('id', $sameCategory->modelKeys())
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit - $sameCategory->count())
                ->get();

        return $sameCategory->concat($others)
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->values()
            ->all();
    }

    public function suggest(?string $term, int $limit): array
    {
        $matches = $this->launchedQuery()
            ->when($term !== null, fn (Builder $query) => $this->matchTerm($query, (string) $term));

        return [
            'total' => (clone $matches)->count(),
            'items' => $matches
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product): array => $product->toStorefrontArray())
                ->values()
                ->all(),
        ];
    }

    private function matchTerm(Builder $query, string $term): Builder
    {
        $pattern = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $search) use ($pattern): void {
            $search->where('name', 'like', $pattern)
                ->orWhere('short_description', 'like', $pattern)
                ->orWhere('sku', 'like', $pattern);
        });
    }

    private function scopeToCategory(Builder $query, Category $category): Builder
    {
        return $category->isMain()
            ? $query->where('category_id', $category->id)
            : $query->where('subcategory_id', $category->id);
    }

    private function launchedQuery(): Builder
    {
        return Product::query()
            ->with([
                'category',
                'subcategory',
                'images',
                'sizes',
                'colors',
                'variants.size',
                'variants.color',
            ])
            ->where('is_unlaunched', false);
    }
}
