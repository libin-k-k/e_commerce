<?php

namespace App\Modules\Product\Enums;

enum ProductSort: string
{
    case Popular = 'popular';
    case Newest = 'newest';
    case PriceLowToHigh = 'price_asc';
    case PriceHighToLow = 'price_desc';
    case TopRated = 'rating';

    public function label(): string
    {
        return match ($this) {
            self::Popular => 'Popularity',
            self::Newest => 'Newest first',
            self::PriceLowToHigh => 'Price: Low to High',
            self::PriceHighToLow => 'Price: High to Low',
            self::TopRated => 'Customer rating',
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
