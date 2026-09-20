<?php

namespace App\Modules\Product\Http\Controllers\Admin;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Product\Http\Requests\Admin\StoreProductRequest;
use App\Modules\Product\Http\Requests\Admin\UpdateProductRequest;
use App\Modules\Product\Services\ProductAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductAdminService $productAdminService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Products/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Products/index'),
            'products' => $this->productAdminService->list(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Products/Create', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Products/create'),
            'options' => $this->productAdminService->formOptions(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productAdminService->create(
            $request->safe()->except(['main_image', 'additional_images', 'remove_image_ids']),
            $request->file('main_image'),
            $this->uploadedFiles($request->file('additional_images')),
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created.');
    }

    public function edit(int $product): Response
    {
        $model = $this->productAdminService->findOrFail($product);

        return Inertia::render('Admin/Pages/Products/Edit', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Products/edit'),
            'product' => $model->toAdminArray(),
            'options' => $this->productAdminService->formOptions(),
        ]);
    }

    public function update(UpdateProductRequest $request, int $product): RedirectResponse
    {
        $model = $this->productAdminService->findOrFail($product);
        $removeIds = $request->input('remove_image_ids', []);
        if (is_string($removeIds)) {
            $decoded = json_decode($removeIds, true);
            $removeIds = is_array($decoded) ? $decoded : [];
        }

        $this->productAdminService->update(
            $model,
            $request->safe()->except(['main_image', 'additional_images', 'remove_image_ids']),
            $request->file('main_image'),
            $this->uploadedFiles($request->file('additional_images')),
            array_map('intval', $removeIds),
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(int $product): RedirectResponse
    {
        $model = $this->productAdminService->findOrFail($product);
        $this->productAdminService->delete($model);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    /**
     * @return list<UploadedFile>
     */
    private function uploadedFiles(mixed $files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            fn ($file): bool => $file instanceof UploadedFile,
        ));
    }
}
