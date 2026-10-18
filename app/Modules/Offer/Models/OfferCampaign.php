<?php

namespace App\Modules\Offer\Models;

use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class OfferCampaign extends Model
{
    public const ProductLimit = 4;

    protected $fillable = [
        'title',
        'starts_at',
        'ends_at',
        'countdown_enabled',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'countdown_enabled' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'offer_campaign_product')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function scopeLive(Builder $query, ?Carbon $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $window) use ($at): void {
                $window->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function (Builder $window) use ($at): void {
                $window->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            });
    }

    public function isLive(?Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at !== null && $at->lt($this->starts_at)) {
            return false;
        }

        return $this->ends_at === null || $at->lte($this->ends_at);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'starts_at' => $this->starts_at?->format('Y-m-d\TH:i'),
            'ends_at' => $this->ends_at?->format('Y-m-d\TH:i'),
            'countdown_enabled' => $this->countdown_enabled,
            'is_active' => $this->is_active,
            'is_live' => $this->isLive(),
            'sort_order' => $this->sort_order,
            'product_ids' => $this->products->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
            'products' => $this->products->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
            ])->values()->all(),
        ];
    }
}
