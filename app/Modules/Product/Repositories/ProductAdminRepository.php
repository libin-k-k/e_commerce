<?php

namespace App\Modules\Product\Repositories;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Contracts\ProductAdminRepositoryInterface;
use Illuminate\Support\Collection;

class ProductAdminRepository implements ProductAdminRepositoryInterface
{
    public function allOrdered(): Collection
    {
        return Product::query()
            ->with($this->defaultWith())
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function find(int $id): ?Product
    {
        return Product::query()
            ->with($this->defaultWith())
            ->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::query()
            ->with($this->defaultWith())
            ->where('slug', $slug)
            ->first();
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh()->load($this->defaultWith());
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function skuExists(string $sku, ?int $ignoreId = null): bool
    {
        return Product::query()
            ->where('sku', $sku)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    /**
     * @return list<string>
     */
    private function defaultWith(): array
    {
        return [
            'category',
            'subcategory',
            'images',
            'sizes',
            'colors',
            'variants.size',
            'variants.color',
        ];
    }
}
