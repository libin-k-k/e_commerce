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
        [$minimum, $maximum] = $this->bounds();

        return ($minimum === null || $price >= $minimum)
            && ($maximum === null || $price < $maximum);
    }

    /**
     * Inclusive minimum and exclusive maximum. Null leaves that side open.
     * Bounds stay integers because SQLite mis-compares this expression when the
     * placeholder is bound as a float or a string.
     *
     * @return array{0: ?int, 1: ?int}
     */
    public function bounds(): array
    {
        return match ($this) {
            self::Under100 => [null, 100],
            self::From100To500 => [100, 500],
            self::From500To1000 => [500, 1000],
            self::Above1000 => [1000, null],
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
