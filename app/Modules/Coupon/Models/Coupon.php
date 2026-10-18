<?php

namespace App\Modules\Coupon\Models;

use App\Modules\Coupon\Enums\CouponType;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'max_discount_amount',
        'min_order_amount',
        'max_order_amount',
        'starts_at',
        'ends_at',
        'total_usage_limit',
        'per_user_usage_limit',
        'first_order_only',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'total_usage_limit' => 'integer',
            'per_user_usage_limit' => 'integer',
            'first_order_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product')->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function hasValidityWindow(): bool
    {
        return $this->starts_at !== null || $this->ends_at !== null;
    }

    public function isWithinValidity(?\DateTimeInterface $at = null): bool
    {
        $at = $at ?? now();

        if ($this->starts_at !== null && $at->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at !== null && $at->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'value' => (float) $this->value,
            'max_discount_amount' => $this->max_discount_amount !== null ? (float) $this->max_discount_amount : null,
            'min_order_amount' => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'max_order_amount' => $this->max_order_amount !== null ? (float) $this->max_order_amount : null,
            'starts_at' => $this->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $this->ends_at?->format('Y-m-d\TH:i'),
            'has_validity' => $this->hasValidityWindow(),
            'total_usage_limit' => $this->total_usage_limit,
            'per_user_usage_limit' => $this->per_user_usage_limit,
            'first_order_only' => $this->first_order_only,
            'is_active' => $this->is_active,
            'product_ids' => $this->relationLoaded('products')
                ? $this->products->pluck('id')->map(fn ($id): int => (int) $id)->values()->all()
                : $this->products()->pluck('products.id')->map(fn ($id): int => (int) $id)->values()->all(),
            'redemptions_count' => (int) ($this->redemptions_count ?? (
                $this->relationLoaded('redemptions')
                    ? $this->redemptions->count()
                    : $this->redemptions()->count()
            )),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
