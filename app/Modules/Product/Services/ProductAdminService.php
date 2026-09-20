<?php

namespace App\Modules\Product\Services;

use App\Modules\Banner\Services\WebpImageConverter;
use App\Modules\Category\Models\Category;
use App\Modules\Category\Repositories\Contracts\CategoryRepositoryInterface;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductColor;
use App\Modules\Product\Models\ProductSize;
use App\Modules\Product\Models\ProductVariant;
use App\Modules\Product\Repositories\Contracts\ProductAdminRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductAdminService
{
    public function __construct(
        private readonly ProductAdminRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly WebpImageConverter $webpImageConverter,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function list(): Collection
    {
        return $this->products->allOrdered()->map(
            fn (Product $product): array => $product->toAdminArray(),
        );
    }

    public function findOrFail(int $id): Product
    {
        $product = $this->products->find($id);

        if ($product === null) {
            abort(404);
        }

        return $product;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>|null  $additionalImages
     */
    public function create(array $data, ?UploadedFile $mainImage = null, ?array $additionalImages = null): Product
    {
        return DB::transaction(function () use ($data, $mainImage, $additionalImages): Product {
            $payload = $this->normalize($data);

            if ($mainImage !== null) {
                $payload['main_image_path'] = $this->webpImageConverter->store($mainImage, 'products/main', 1200);
            }

            $product = $this->products->create($payload);
            $this->syncOptionsAndVariants($product, $data, $payload);
            $this->storeAdditionalImages($product, $additionalImages ?? []);

            return $this->findOrFail($product->id);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>|null  $additionalImages
     * @param  list<int>|null  $removeImageIds
     */
    public function update(
        Product $product,
        array $data,
        ?UploadedFile $mainImage = null,
        ?array $additionalImages = null,
        ?array $removeImageIds = null,
    ): Product {
        return DB::transaction(function () use ($product, $data, $mainImage, $additionalImages, $removeImageIds): Product {
            $payload = $this->normalize($data, $product->id);

            if ($mainImage !== null) {
                $this->deleteStoredImage($product->main_image_path);
                $payload['main_image_path'] = $this->webpImageConverter->store($mainImage, 'products/main', 1200);
            }

            $this->products->update($product, $payload);
            $this->removeImages($product, $removeImageIds ?? []);
            $this->syncOptionsAndVariants($product->fresh(), $data, $payload);
            $this->storeAdditionalImages($product->fresh(), $additionalImages ?? []);

            return $this->findOrFail($product->id);
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $this->deleteStoredImage($product->main_image_path);

            foreach ($product->images as $image) {
                $this->deleteStoredImage($image->path);
            }

            $this->products->delete($product);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        $mains = $this->categories->mainsWithChildren();

        return [
            'categories' => $mains->map(fn (Category $category): array => [
                'value' => $category->id,
                'label' => $category->name,
                'children' => $category->children->map(fn (Category $child): array => [
                    'value' => $child->id,
                    'label' => $child->name,
                ])->values()->all(),
            ])->values()->all(),
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
        $sku = strtoupper(trim((string) ($data['sku'] ?? '')));

        if ($slug === '') {
            $slug = 'product-'.Str::lower(Str::random(6));
        }

        if ($sku === '') {
            throw ValidationException::withMessages(['sku' => 'SKU is required.']);
        }

        if ($this->products->slugExists($slug, $ignoreId)) {
            throw ValidationException::withMessages(['slug' => 'This slug is already taken.']);
        }

        if ($this->products->skuExists($sku, $ignoreId)) {
            throw ValidationException::withMessages(['sku' => 'This SKU is already taken.']);
        }

        $categoryId = (int) ($data['category_id'] ?? 0);
        $subcategoryId = $data['subcategory_id'] ?? null;
        if ($subcategoryId === '' || $subcategoryId === 'null') {
            $subcategoryId = null;
        } else {
            $subcategoryId = (int) $subcategoryId;
        }

        $category = Category::query()->find($categoryId);
        if ($category === null || $category->parent_id !== null) {
            throw ValidationException::withMessages(['category_id' => 'Select a valid main category.']);
        }

        if ($subcategoryId !== null) {
            $sub = Category::query()->find($subcategoryId);
            if ($sub === null || (int) $sub->parent_id !== $categoryId) {
                throw ValidationException::withMessages(['subcategory_id' => 'Select a sub category under the main category.']);
            }
        }

        $price = (float) ($data['price'] ?? 0);
        $salePrice = $data['sale_price'] ?? null;
        if ($salePrice === '' || $salePrice === null) {
            $salePrice = null;
        } else {
            $salePrice = (float) $salePrice;
            if ($salePrice >= $price) {
                throw ValidationException::withMessages(['sale_price' => 'Sale price must be less than price.']);
            }
        }

        $description = $data['description'] ?? null;
        if (is_string($description)) {
            $description = $this->sanitizeHtml($description);
        }

        return [
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'short_description' => ($data['short_description'] ?? null) ?: null,
            'description' => $description ?: null,
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => (int) ($data['stock'] ?? 0),
            'rating' => round((float) ($data['rating'] ?? 0), 1),
            'is_unlaunched' => (bool) ($data['is_unlaunched'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $basePayload
     */
    private function syncOptionsAndVariants(Product $product, array $data, array $basePayload): void
    {
        $product->variants()->delete();
        $product->sizes()->delete();
        $product->colors()->delete();

        $sizeNames = $this->normalizeSizeNames($data['sizes'] ?? []);
        $colorItems = $this->normalizeColorItems($data['colors'] ?? []);

        /** @var array<string, ProductSize> $sizeMap */
        $sizeMap = [];
        foreach ($sizeNames as $index => $name) {
            $size = $product->sizes()->create([
                'name' => $name,
                'sort_order' => $index,
            ]);
            $sizeMap[$name] = $size;
        }

        /** @var array<string, ProductColor> $colorMap */
        $colorMap = [];
        foreach ($colorItems as $index => $color) {
            $row = $product->colors()->create([
                'name' => $color['name'],
                'hex' => $color['hex'],
                'sort_order' => $index,
            ]);
            $colorMap[$color['name']] = $row;
        }

        $hasOptions = $sizeMap !== [] || $colorMap !== [];
        if (! $hasOptions) {
            $product->update(['stock' => (int) $basePayload['stock']]);

            return;
        }

        $rows = $this->normalizeVariantRows(
            $data['variants'] ?? [],
            array_keys($sizeMap),
            array_keys($colorMap),
            $basePayload,
            (string) $basePayload['sku'],
        );

        $totalStock = 0;
        $seenSkus = [];

        foreach ($rows as $index => $row) {
            $size = $row['size'] !== null ? ($sizeMap[$row['size']] ?? null) : null;
            $color = $row['color'] !== null ? ($colorMap[$row['color']] ?? null) : null;

            if ($row['size'] !== null && $size === null) {
                throw ValidationException::withMessages([
                    'variants' => 'Variant size "'.$row['size'].'" is not in the size list.',
                ]);
            }

            if ($row['color'] !== null && $color === null) {
                throw ValidationException::withMessages([
                    'variants' => 'Variant color "'.$row['color'].'" is not in the color list.',
                ]);
            }

            $variantSku = $row['sku'];
            if ($variantSku !== null) {
                if (isset($seenSkus[$variantSku]) || $variantSku === strtoupper((string) $basePayload['sku'])) {
                    throw ValidationException::withMessages([
                        'variants' => 'Duplicate variant SKU: '.$variantSku,
                    ]);
                }

                if (ProductVariant::query()->where('sku', $variantSku)->exists()) {
                    throw ValidationException::withMessages([
                        'variants' => 'Variant SKU already exists: '.$variantSku,
                    ]);
                }

                $seenSkus[$variantSku] = true;
            }

            if ($row['sale_price'] !== null && $row['sale_price'] >= $row['price']) {
                throw ValidationException::withMessages([
                    'variants' => 'Sale price must be less than price for each variant.',
                ]);
            }

            $sizeId = $size?->id;
            $colorId = $color?->id;

            $product->variants()->create([
                'product_size_id' => $sizeId,
                'product_color_id' => $colorId,
                'combo_key' => ProductVariant::makeComboKey($sizeId, $colorId),
                'sku' => $variantSku,
                'price' => $row['price'],
                'sale_price' => $row['sale_price'],
                'stock' => $row['stock'],
                'sort_order' => $index,
            ]);

            $totalStock += $row['stock'];
        }

        $product->update(['stock' => $totalStock]);
    }

    /**
     * @return list<string>
     */
    private function normalizeSizeNames(mixed $value): array
    {
        $items = $this->decodeList($value);
        $names = [];

        foreach ($items as $item) {
            $name = trim((string) (is_array($item) ? ($item['name'] ?? '') : $item));
            if ($name === '' || in_array($name, $names, true)) {
                continue;
            }
            $names[] = $name;
        }

        return $names;
    }

    /**
     * @return list<array{name: string, hex: ?string}>
     */
    private function normalizeColorItems(mixed $value): array
    {
        $items = $this->decodeList($value);
        $colors = [];
        $seen = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                $name = trim((string) ($item['name'] ?? ''));
                $hex = trim((string) ($item['hex'] ?? ''));
            } else {
                $name = trim((string) $item);
                $hex = '';
            }

            if ($name === '' || isset($seen[$name])) {
                continue;
            }

            $seen[$name] = true;
            $colors[] = [
                'name' => $name,
                'hex' => $hex !== '' ? $hex : null,
            ];
        }

        return $colors;
    }

    /**
     * Build the final matrix rows. Missing combos are filled from base price/stock.
     *
     * @param  list<string>  $sizeNames
     * @param  list<string>  $colorNames
     * @param  array<string, mixed>  $basePayload
     * @return list<array{size: ?string, color: ?string, sku: ?string, price: float, sale_price: ?float, stock: int}>
     */
    private function normalizeVariantRows(
        mixed $value,
        array $sizeNames,
        array $colorNames,
        array $basePayload,
        string $baseSku,
    ): array {
        $submitted = [];
        foreach ($this->decodeList($value) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $size = trim((string) ($item['size'] ?? ''));
            $color = trim((string) ($item['color'] ?? ''));
            $size = $size === '' ? null : $size;
            $color = $color === '' ? null : $color;
            $key = ($size ?? '').'|'.($color ?? '');

            $sale = $item['sale_price'] ?? null;
            if ($sale === '' || $sale === null) {
                $sale = null;
            } else {
                $sale = (float) $sale;
            }

            $sku = strtoupper(trim((string) ($item['sku'] ?? '')));
            if ($sku === '') {
                $sku = null;
            }

            $submitted[$key] = [
                'size' => $size,
                'color' => $color,
                'sku' => $sku,
                'price' => (float) ($item['price'] ?? $basePayload['price']),
                'sale_price' => $sale,
                'stock' => max(0, (int) ($item['stock'] ?? 0)),
            ];
        }

        $expected = $this->expectedCombinations($sizeNames, $colorNames);
        $rows = [];

        foreach ($expected as $index => $combo) {
            $key = ($combo['size'] ?? '').'|'.($combo['color'] ?? '');
            if (isset($submitted[$key])) {
                $rows[] = $submitted[$key];

                continue;
            }

            $suffix = collect([$combo['size'], $combo['color']])
                ->filter()
                ->map(fn (string $part): string => Str::upper(Str::slug($part, '')))
                ->implode('-');

            $rows[] = [
                'size' => $combo['size'],
                'color' => $combo['color'],
                'sku' => $suffix !== '' ? $baseSku.'-'.$suffix : null,
                'price' => (float) $basePayload['price'],
                'sale_price' => $basePayload['sale_price'] !== null ? (float) $basePayload['sale_price'] : null,
                'stock' => (int) $basePayload['stock'],
            ];
        }

        return $rows;
    }

    /**
     * @param  list<string>  $sizeNames
     * @param  list<string>  $colorNames
     * @return list<array{size: ?string, color: ?string}>
     */
    private function expectedCombinations(array $sizeNames, array $colorNames): array
    {
        if ($sizeNames !== [] && $colorNames !== []) {
            $combos = [];
            foreach ($sizeNames as $size) {
                foreach ($colorNames as $color) {
                    $combos[] = ['size' => $size, 'color' => $color];
                }
            }

            return $combos;
        }

        if ($sizeNames !== []) {
            return array_map(fn (string $size): array => ['size' => $size, 'color' => null], $sizeNames);
        }

        return array_map(fn (string $color): array => ['size' => null, 'color' => $color], $colorNames);
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    private function storeAdditionalImages(Product $product, array $files): void
    {
        $sort = (int) $product->images()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $sort++;
            $path = $this->webpImageConverter->store($file, 'products/gallery', 1200);
            $product->images()->create([
                'path' => $path,
                'sort_order' => $sort,
            ]);
        }
    }

    /**
     * @param  list<int|string>  $ids
     */
    private function removeImages(Product $product, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $images = $product->images()->whereIn('id', $ids)->get();
        foreach ($images as $image) {
            $this->deleteStoredImage($image->path);
            $image->delete();
        }
    }

    /**
     * @return list<mixed>
     */
    private function decodeList(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? array_values($value) : [];
    }

    private function sanitizeHtml(string $html): string
    {
        $allowed = [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'br', 'hr',
            'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'ul', 'ol', 'li',
            'a', 'blockquote',
            'div', 'span',
        ];

        $document = new \DOMDocument;
        $internal = libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div id="root">'.$html.'</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($internal);

        $root = $document->getElementById('root');
        if ($root === null) {
            return '';
        }

        $this->sanitizeDomNode($root, $allowed);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    /**
     * @param  list<string>  $allowed
     */
    private function sanitizeDomNode(\DOMNode $node, array $allowed): void
    {
        if (! $node->hasChildNodes()) {
            return;
        }

        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                $node->removeChild($child);

                continue;
            }

            /** @var \DOMElement $child */
            $tag = strtolower($child->tagName);

            if (! in_array($tag, $allowed, true)) {
                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            $attributes = [];
            foreach ($child->attributes ?? [] as $attribute) {
                $attributes[] = $attribute->name;
            }

            foreach ($attributes as $attributeName) {
                $name = strtolower($attributeName);
                if ($tag === 'a' && $name === 'href') {
                    $href = trim((string) $child->getAttribute('href'));
                    if ($href === '' || ! preg_match('/^(https?:\/\/|\/|#|mailto:)/i', $href)) {
                        $child->removeAttribute('href');
                    }

                    continue;
                }

                if (in_array($name, ['class', 'title'], true)) {
                    continue;
                }

                $child->removeAttribute($attributeName);
            }

            if ($tag === 'a' && ! $child->hasAttribute('href')) {
                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            if ($tag === 'a') {
                $child->setAttribute('rel', 'noopener noreferrer');
                $child->setAttribute('target', '_blank');
            }

            $this->sanitizeDomNode($child, $allowed);
        }
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, 'assets/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
