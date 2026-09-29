<?php

namespace App\Modules\Product\Enums;

enum PriceRange: string
{
    case Under100 = 'under-100';
    case From100To500 = '100-500';
    case From500To1000 = '500-1000';
    case Above1000 = 'above-1000';

    public function label(): string
    {
        return match ($this) {
            self::Under100 => 'Under ₹100',
            self::From100To500 => '₹100 – ₹500',
            self::From500To1000 => '₹500 – ₹1,000',
            self::Above1000 => 'Above ₹1,000',
        };
    }

    public function contains(float $price): bool
    {
        return match ($this) {
            self::Under100 => $price < 100,
            self::From100To500 => $price >= 100 && $price < 500,
            self::From500To1000 => $price >= 500 && $price < 1000,
            self::Above1000 => $price >= 1000,
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases(),
        );
    }
}
