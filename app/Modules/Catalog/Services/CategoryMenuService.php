<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Category\Services\CategoryStorefrontService;

class CategoryMenuService
{
    public function __construct(
        private readonly CategoryStorefrontService $categoryStorefrontService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function menu(): array
    {
        return $this->categoryStorefrontService->menu();
    }
}
