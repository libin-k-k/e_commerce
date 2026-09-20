<?php

namespace App\Modules\Category\Repositories\Contracts;

use App\Modules\Category\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /**
     * @return Collection<int, Category>
     */
    public function mainsWithChildren(): Collection;

    /**
     * @return Collection<int, Category>
     */
    public function mainOptions(): Collection;

    /**
     * @return Collection<int, Category>
     */
    public function activeMainsWithChildren(): Collection;

    public function find(int $id): ?Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;
}
