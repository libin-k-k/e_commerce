<?php

namespace App\Modules\Category\Services;

use App\Modules\Banner\Services\WebpImageConverter;
use App\Modules\Category\Models\Category;
use App\Modules\Category\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryAdminService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly WebpImageConverter $webpImageConverter,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listTree(): Collection
    {
        return $this->categories->mainsWithChildren()->map(
            fn (Category $category): array => $category->toAdminArray(withChildren: true),
        );
    }

    public function findOrFail(int $id): Category
    {
        $category = $this->categories->find($id);

        if ($category === null) {
            abort(404);
        }

        return $category;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Category
    {
        $payload = $this->normalize($data);

        if ($image !== null) {
            $payload['image_path'] = $this->webpImageConverter->store($image, 'categories', 800);
        }

        return $this->categories->create($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $payload = $this->normalize($data, $category->id);

        if (($payload['parent_id'] ?? null) === $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        if ($category->isMain() && ($payload['parent_id'] ?? null) !== null && $category->children()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Move or remove sub categories before nesting this main category.',
            ]);
        }

        if ($image !== null) {
            $this->deleteStoredImage($category->image_path);
            $payload['image_path'] = $this->webpImageConverter->store($image, 'categories', 800);
        }

        return $this->categories->update($category, $payload);
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Delete or reassign sub categories before deleting this main category.',
            ]);
        }

        $this->deleteStoredImage($category->image_path);
        $this->categories->delete($category);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(?int $ignoreId = null): array
    {
        $parents = $this->categories->mainOptions()
            ->when($ignoreId !== null, fn (Collection $items) => $items->reject(
                fn (Category $category): bool => $category->id === $ignoreId,
            ))
            ->map(fn (Category $category): array => [
                'value' => $category->id,
                'label' => $category->name,
            ])
            ->values()
            ->all();

        return [
            'parents' => [
                ['value' => null, 'label' => 'Main category (no parent)'],
                ...$parents,
            ],
            'imageHint' => '1:1 crop · auto WebP under 200KB',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?int $ignoreId = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? Str::slug($slug) : Str::slug($name);

        if ($slug === '') {
            $slug = 'category-'.Str::lower(Str::random(6));
        }

        if ($this->categories->slugExists($slug, $ignoreId)) {
            throw ValidationException::withMessages([
                'slug' => 'This slug is already taken.',
            ]);
        }

        $parentId = $data['parent_id'] ?? null;
        if ($parentId === '' || $parentId === 'null') {
            $parentId = null;
        }

        return [
            'parent_id' => $parentId !== null ? (int) $parentId : null,
            'name' => $name,
            'slug' => $slug,
            'description' => ($data['description'] ?? null) ?: null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, 'assets/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
