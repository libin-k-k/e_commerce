<?php

namespace App\Modules\Category\Http\Controllers\Admin;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Category\Http\Requests\Admin\StoreCategoryRequest;
use App\Modules\Category\Http\Requests\Admin\UpdateCategoryRequest;
use App\Modules\Category\Services\CategoryAdminService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryAdminService $categoryAdminService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Categories/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Categories/index'),
            'categories' => $this->categoryAdminService->listTree(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Categories/Create', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Categories/create'),
            'options' => $this->categoryAdminService->formOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryAdminService->create(
            $request->safe()->except(['image']),
            $request->file('image'),
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(int $category): Response
    {
        $model = $this->categoryAdminService->findOrFail($category);

        return Inertia::render('Admin/Pages/Categories/Edit', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Categories/edit'),
            'category' => $model->toAdminArray(),
            'options' => $this->categoryAdminService->formOptions($model->id),
        ]);
    }

    public function update(UpdateCategoryRequest $request, int $category): RedirectResponse
    {
        $model = $this->categoryAdminService->findOrFail($category);

        $this->categoryAdminService->update(
            $model,
            $request->safe()->except(['image']),
            $request->file('image'),
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(int $category): RedirectResponse
    {
        $model = $this->categoryAdminService->findOrFail($category);
        $this->categoryAdminService->delete($model);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }
}
