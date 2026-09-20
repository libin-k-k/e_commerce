<?php

namespace App\Modules\Product\Repositories\Contracts;

use App\Modules\Product\Models\Product;
use Illuminate\Support\Collection;

interface ProductAdminRepositoryInterface
{
    /**
     * @return Collection<int, Product>
     */
    public function allOrdered(): Collection;

    public function find(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;

    public function skuExists(string $sku, ?int $ignoreId = null): bool;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
