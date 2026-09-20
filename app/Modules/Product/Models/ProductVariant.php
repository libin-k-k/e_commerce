<?php

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'product_size_id',
        'product_color_id',
        'combo_key',
        'sku',
        'price',
        'sale_price',
        'stock',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'product_size_id' => 'integer',
            'product_color_id' => 'integer',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }

    public static function makeComboKey(?int $sizeId, ?int $colorId): string
    {
        return 's:'.($sizeId ?? 0).'|c:'.($colorId ?? 0);
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null
            && (float) $this->sale_price > 0
            && (float) $this->sale_price < (float) $this->price;
    }

    public function effectivePrice(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size?->name,
            'color' => $this->color?->name,
            'size_id' => $this->product_size_id,
            'color_id' => $this->product_color_id,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'stock' => $this->stock,
            'sort_order' => $this->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size?->name,
            'color' => $this->color?->name,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'salePrice' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effectivePrice' => $this->effectivePrice(),
            'stock' => $this->stock,
            'inStock' => $this->stock > 0,
            'lowStock' => $this->stock > 0 && $this->stock <= Product::LowStockThreshold,
        ];
    }
}
