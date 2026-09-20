<?php

namespace App\Modules\Product\Repositories\Contracts;

interface ProductRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array;

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
     * @return list<array<string, mixed>>
     */
    public function related(string $slug, int $limit = 4): array;
}
