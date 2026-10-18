<?php

namespace App\Modules\Product\Models;

use App\Modules\Product\Enums\QuantityDiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuantityDiscount extends Model
{
    protected $fillable = [
        'product_id',
        'min_quantity',
        'type',
        'value',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'min_quantity' => 'integer',
            'type' => QuantityDiscountType::class,
            'value' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'min_quantity' => $this->min_quantity,
            'type' => $this->type->value,
            'value' => (float) $this->value,
            'sort_order' => $this->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        return [
            'minQuantity' => $this->min_quantity,
            'type' => $this->type->value,
            'value' => (float) $this->value,
            'label' => $this->storefrontLabel(),
        ];
    }

    public function storefrontLabel(): string
    {
        $qty = $this->min_quantity;
        $value = (float) $this->value;

        return match ($this->type) {
            QuantityDiscountType::Bundle => 'Buy '.$qty.' for ₹'.number_format($value, 2),
            QuantityDiscountType::Percent => 'Buy '.$qty.'+ get '.rtrim(rtrim(number_format($value, 2), '0'), '.').'% off',
            QuantityDiscountType::Fixed => 'Buy '.$qty.'+ save ₹'.number_format($value, 2),
        };
    }
}
