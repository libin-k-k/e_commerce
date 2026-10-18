<?php

namespace App\Modules\Coupon\Services;

use App\Modules\Coupon\Enums\CouponType;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponAdminService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function list(): Collection
    {
        return Coupon::query()
            ->withCount('redemptions')
            ->with('products:id,name')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Coupon $coupon): array => $coupon->toAdminArray());
    }

    public function findOrFail(int $id): Coupon
    {
        return Coupon::query()->with('products:id,name')->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Coupon
    {
        return DB::transaction(function () use ($data): Coupon {
            $payload = $this->normalize($data);
            $productIds = $payload['product_ids'];
            unset($payload['product_ids']);

            $coupon = Coupon::query()->create($payload);
            $coupon->products()->sync($productIds);

            return $coupon->load('products:id,name');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Coupon $coupon, array $data): Coupon
    {
        return DB::transaction(function () use ($coupon, $data): Coupon {
            $payload = $this->normalize($data, $coupon->id);
            $productIds = $payload['product_ids'];
            unset($payload['product_ids']);

            $coupon->update($payload);
            $coupon->products()->sync($productIds);

            return $coupon->fresh(['products:id,name']);
        });
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->delete();
    }

    /**
     * @return array{products: list<array{value: int, label: string}>}
     */
    public function formOptions(): array
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku'])
            ->map(fn (Product $product): array => [
                'value' => $product->id,
                'label' => $product->name.' ('.$product->sku.')',
            ])
            ->values()
            ->all();

        return [
            'products' => $products,
            'types' => [
                ['value' => CouponType::Percent->value, 'label' => CouponType::Percent->label()],
                ['value' => CouponType::Fixed->value, 'label' => CouponType::Fixed->label()],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?int $ignoreId = null): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        if ($code === '') {
            throw ValidationException::withMessages(['code' => 'Coupon code is required.']);
        }

        $exists = Coupon::query()
            ->where('code', $code)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['code' => 'This coupon code is already taken.']);
        }

        $type = CouponType::tryFrom((string) ($data['type'] ?? ''));
        if ($type === null) {
            throw ValidationException::withMessages(['type' => 'Select a valid coupon type.']);
        }

        $value = (float) ($data['value'] ?? 0);
        if ($value <= 0) {
            throw ValidationException::withMessages(['value' => 'Discount value must be greater than 0.']);
        }

        if ($type === CouponType::Percent && $value > 100) {
            throw ValidationException::withMessages(['value' => 'Percentage cannot exceed 100.']);
        }

        $maxDiscount = $this->nullableFloat($data['max_discount_amount'] ?? null);
        if ($type === CouponType::Percent) {
            if ($maxDiscount === null || $maxDiscount <= 0) {
                throw ValidationException::withMessages([
                    'max_discount_amount' => 'Percentage coupons require a max discount amount (₹).',
                ]);
            }
        }

        $hasValidity = (bool) ($data['has_validity'] ?? false);
        $startsAt = null;
        $endsAt = null;

        if ($hasValidity) {
            $startsAt = filled($data['starts_at'] ?? null) ? $data['starts_at'] : null;
            $endsAt = filled($data['ends_at'] ?? null) ? $data['ends_at'] : null;

            if ($startsAt === null && $endsAt === null) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Set from date, to date, or both when validity is enabled.',
                ]);
            }

            if ($startsAt !== null && $endsAt !== null && $startsAt > $endsAt) {
                throw ValidationException::withMessages([
                    'ends_at' => 'To date must be after from date.',
                ]);
            }
        }

        $minOrder = $this->nullableFloat($data['min_order_amount'] ?? null);
        $maxOrder = $this->nullableFloat($data['max_order_amount'] ?? null);
        if ($minOrder !== null && $maxOrder !== null && $maxOrder < $minOrder) {
            throw ValidationException::withMessages([
                'max_order_amount' => 'Max order must be greater than or equal to min order.',
            ]);
        }

        $productIds = collect($data['product_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($productIds !== []) {
            $found = Product::query()->whereIn('id', $productIds)->pluck('id')->map(fn ($id): int => (int) $id)->all();
            if (count($found) !== count($productIds)) {
                throw ValidationException::withMessages(['product_ids' => 'One or more selected products are invalid.']);
            }
        }

        return [
            'code' => $code,
            'name' => filled($data['name'] ?? null) ? trim((string) $data['name']) : null,
            'type' => $type,
            'value' => round($value, 2),
            'max_discount_amount' => $type === CouponType::Percent ? $maxDiscount : $maxDiscount,
            'min_order_amount' => $minOrder,
            'max_order_amount' => $maxOrder,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'total_usage_limit' => $this->nullableInt($data['total_usage_limit'] ?? null),
            'per_user_usage_limit' => $this->nullableInt($data['per_user_usage_limit'] ?? null),
            'first_order_only' => (bool) ($data['first_order_only'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'product_ids' => $productIds,
        ];
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
