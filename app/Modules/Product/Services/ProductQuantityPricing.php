<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\Enums\QuantityDiscountType;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductQuantityDiscount;
use Illuminate\Support\Collection;

class ProductQuantityPricing
{
    /**
     * @return array{
     *     unit_price: float,
     *     line_total: float,
     *     base_line_total: float,
     *     quantity_discount: ?array{min_quantity: int, type: string, value: float, label: string},
     *     savings: float
     * }
     */
    public function priceLine(Product $product, float $unitPrice, int $quantity): array
    {
        $quantity = max(1, $quantity);
        $unitPrice = max(0.0, $unitPrice);
        $baseLine = round($unitPrice * $quantity, 2);

        $tiers = $this->tiersFor($product);
        if ($tiers->isEmpty()) {
            return [
                'unit_price' => $unitPrice,
                'line_total' => $baseLine,
                'base_line_total' => $baseLine,
                'quantity_discount' => null,
                'savings' => 0.0,
            ];
        }

        $bestTotal = $baseLine;
        $bestTier = null;

        foreach ($tiers as $tier) {
            if ($quantity < $tier->min_quantity) {
                continue;
            }

            $total = $this->lineTotalForTier($tier, $unitPrice, $quantity);
            if ($total < $bestTotal) {
                $bestTotal = $total;
                $bestTier = $tier;
            }
        }

        $bestTotal = round(max(0.0, $bestTotal), 2);
        $effectiveUnit = round($bestTotal / $quantity, 2);

        return [
            'unit_price' => $effectiveUnit,
            'line_total' => $bestTotal,
            'base_line_total' => $baseLine,
            'quantity_discount' => $bestTier === null ? null : [
                'min_quantity' => $bestTier->min_quantity,
                'type' => $bestTier->type->value,
                'value' => (float) $bestTier->value,
                'label' => $bestTier->storefrontLabel(),
            ],
            'savings' => round(max(0.0, $baseLine - $bestTotal), 2),
        ];
    }

    private function lineTotalForTier(ProductQuantityDiscount $tier, float $unitPrice, int $quantity): float
    {
        $value = (float) $tier->value;

        return match ($tier->type) {
            QuantityDiscountType::Bundle => $this->bundleLineTotal($tier->min_quantity, $value, $unitPrice, $quantity),
            QuantityDiscountType::Percent => round($unitPrice * $quantity * (1 - min(100.0, max(0.0, $value)) / 100), 2),
            QuantityDiscountType::Fixed => round(max(0.0, ($unitPrice * $quantity) - max(0.0, $value)), 2),
        };
    }

    private function bundleLineTotal(int $packSize, float $packPrice, float $unitPrice, int $quantity): float
    {
        $packs = intdiv($quantity, $packSize);
        $remainder = $quantity % $packSize;

        return round(($packs * $packPrice) + ($remainder * $unitPrice), 2);
    }

    /**
     * @return Collection<int, ProductQuantityDiscount>
     */
    private function tiersFor(Product $product): Collection
    {
        $tiers = $product->relationLoaded('quantityDiscounts')
            ? $product->quantityDiscounts
            : $product->quantityDiscounts()->get();

        return $tiers
            ->sortByDesc(fn (ProductQuantityDiscount $tier): int => $tier->min_quantity)
            ->values();
    }
}
