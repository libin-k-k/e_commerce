<?php

namespace App\Modules\Product\Repositories;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function all(): array
    {
        return $this->launchedQuery()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->values()
            ->all();
    }

    public function findBySlug(string $slug): ?array
    {
        $product = $this->launchedQuery()
            ->where('slug', $slug)
            ->first();

        return $product?->toStorefrontArray();
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

        return $this->launchedQuery()
            ->when($current !== null, function ($query) use ($current): void {
                $query->where('id', '!=', $current->id)
                    ->where('category_id', $current->category_id);
            }, function ($query) use ($slug): void {
                $query->where('slug', '!=', $slug);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->values()
            ->all();
    }

    private function launchedQuery()
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
