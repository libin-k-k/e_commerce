<?php

namespace App\Modules\Product\Models;

use App\Core\Media\PublicUrl;
use App\Modules\Category\Models\Category;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const LowStockThreshold = 10;

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'sale_price',
        'stock',
        'rating',
        'main_image_path',
        'is_unlaunched',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'subcategory_id' => 'integer',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'rating' => 'decimal:1',
            'is_unlaunched' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort_order')->orderBy('id');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function hasVariants(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
    }

    public function mainImageUrl(): ?string
    {
        return $this->resolveImageUrl($this->main_image_path);
    }

    public function isOnSale(): bool
    {
        $price = $this->displayPrice();
        $compare = $this->displayCompareAtPrice();

        return $compare !== null && $compare > $price;
    }

    public function effectivePrice(): float
    {
        return $this->displayPrice();
    }

    public function availableStock(): int
    {
        if ($this->hasVariants()) {
            $variants = $this->relationLoaded('variants')
                ? $this->variants
                : $this->variants()->get();

            return (int) $variants->sum('stock');
        }

        return (int) $this->stock;
    }

    public function isLowStock(?int $stock = null): bool
    {
        $qty = $stock ?? $this->availableStock();

        return $qty > 0 && $qty <= self::LowStockThreshold;
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        $sizes = $this->relationLoaded('sizes') ? $this->sizes : $this->sizes()->get();
        $colors = $this->relationLoaded('colors') ? $this->colors : $this->colors()->get();
        $variants = $this->relationLoaded('variants')
            ? $this->variants->loadMissing(['size', 'color'])
            : $this->variants()->with(['size', 'color'])->get();
        $stock = $this->availableStock();

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'category_name' => $this->category?->name,
            'subcategory_name' => $this->subcategory?->name,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'stock' => $stock,
            'is_low_stock' => $this->isLowStock($stock),
            'low_stock_threshold' => self::LowStockThreshold,
            'rating' => (float) $this->rating,
            'main_image_path' => $this->main_image_path,
            'main_image_url' => $this->mainImageUrl(),
            'is_unlaunched' => $this->is_unlaunched,
            'sort_order' => $this->sort_order,
            'images' => $this->images->map(fn (ProductImage $image): array => [
                'id' => $image->id,
                'path' => $image->path,
                'url' => $image->url(),
                'sort_order' => $image->sort_order,
            ])->values()->all(),
            'sizes' => $sizes->map(fn (ProductSize $size): array => [
                'id' => $size->id,
                'name' => $size->name,
            ])->values()->all(),
            'colors' => $colors->map(fn (ProductColor $color): array => [
                'id' => $color->id,
                'name' => $color->name,
                'hex' => $color->hex,
            ])->values()->all(),
            'variants' => $variants->map(fn (ProductVariant $variant): array => $variant->toAdminArray())->values()->all(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Storefront payload matching existing React product pages.
     *
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        $variants = $this->relationLoaded('variants')
            ? $this->variants->loadMissing(['size', 'color'])
            : $this->variants()->with(['size', 'color'])->get();

        $sizes = $this->relationLoaded('sizes')
            ? $this->sizes
            : $this->sizes()->get();
        $colors = $this->relationLoaded('colors')
            ? $this->colors
            : $this->colors()->get();

        $effective = $this->displayPrice();
        $compareAt = $this->displayCompareAtPrice();
        $stock = $this->availableStock();

        $images = $this->images->map(fn (ProductImage $image): string => (string) $image->url())->filter()->values()->all();
        $main = $this->mainImageUrl();

        if ($main !== null && ! in_array($main, $images, true)) {
            array_unshift($images, $main);
        }

        if ($images === [] && $main !== null) {
            $images = [$main];
        }

        $badge = null;
        if ($compareAt !== null && $compareAt > $effective) {
            $off = (int) round((1 - ($effective / $compareAt)) * 100);
            $badge = $off.'% off';
        }

        $specs = array_values(array_filter([
            ['label' => 'SKU', 'value' => $this->sku],
            ['label' => 'Category', 'value' => $this->category?->name],
            ['label' => 'Sub category', 'value' => $this->subcategory?->name],
            [
                'label' => 'Available sizes',
                'value' => $sizes->isNotEmpty() ? $sizes->pluck('name')->implode(', ') : null,
            ],
            [
                'label' => 'Available colors',
                'value' => $colors->isNotEmpty() ? $colors->pluck('name')->implode(', ') : null,
            ],
            ['label' => 'Rating', 'value' => number_format((float) $this->rating, 1).' / 5'],
            [
                'label' => 'Availability',
                'value' => $stock > 0 ? 'In stock' : 'Out of stock',
            ],
        ], fn (array $row): bool => filled($row['value'])));

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->formatMoney($effective),
            'priceValue' => $effective,
            'compareAtPrice' => $compareAt !== null && $compareAt > $effective
                ? $this->formatMoney($compareAt)
                : null,
            'badge' => $badge,
            'stock' => $stock,
            'inStock' => $stock > 0,
            'lowStock' => $this->isLowStock($stock),
            'lowStockThreshold' => self::LowStockThreshold,
            'rating' => (float) $this->rating,
            'reviewCount' => 0,
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'specs' => $specs,
            'categoryName' => $this->category?->name,
            'subcategoryName' => $this->subcategory?->name,
            'colors' => $colors->pluck('name')->values()->all(),
            'colorOptions' => $colors->map(fn (ProductColor $color): array => [
                'name' => $color->name,
                'hex' => $color->hex,
            ])->values()->all(),
            'sizes' => $sizes->pluck('name')->values()->all(),
            'variants' => $variants->map(fn (ProductVariant $variant): array => $variant->toStorefrontArray())->values()->all(),
            'image' => $images[0] ?? $main,
            'images' => $images,
            'category' => $this->category?->slug,
            'subcategory' => $this->subcategory?->slug,
            'meta_title' => $this->name,
            'meta_description' => $this->short_description,
            'meta_keywords' => array_filter([$this->name, $this->category?->name, $this->sku]),
            'is_indexable' => ! $this->is_unlaunched,
        ];
    }

    private function displayPrice(): float
    {
        if (! $this->hasVariants()) {
            return $this->baseEffectivePrice();
        }

        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        $priced = $variants->map(fn (ProductVariant $variant): float => $variant->effectivePrice());

        return $priced->isEmpty() ? $this->baseEffectivePrice() : (float) $priced->min();
    }

    private function displayCompareAtPrice(): ?float
    {
        if (! $this->hasVariants()) {
            return $this->isBaseOnSale() ? (float) $this->price : null;
        }

        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        $onSale = $variants->filter(fn (ProductVariant $variant): bool => $variant->isOnSale());

        if ($onSale->isEmpty()) {
            return null;
        }

        return (float) $onSale->max(fn (ProductVariant $variant): float => (float) $variant->price);
    }

    private function baseEffectivePrice(): float
    {
        return $this->isBaseOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    private function isBaseOnSale(): bool
    {
        return $this->sale_price !== null
            && (float) $this->sale_price > 0
            && (float) $this->sale_price < (float) $this->price;
    }

    private function formatMoney(float $amount): string
    {
        return '₹'.Number::format($amount, precision: 2);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        return PublicUrl::for($path);
    }
}
