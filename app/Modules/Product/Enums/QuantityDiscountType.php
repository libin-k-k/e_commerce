<?php

namespace App\Modules\Product\Enums;

enum QuantityDiscountType: string
{
    case Bundle = 'bundle';
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Bundle => 'Bundle total',
            self::Percent => 'Percent off',
            self::Fixed => 'Fixed off',
        };
    }
}
