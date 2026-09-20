<?php

namespace App\Modules\Banner\Enums;

enum BannerPosition: string
{
    case Hero = 'hero';
    case Middle = 'middle';
    case Footer = 'footer';
    case OfferZone = 'offer_zone';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero banner',
            self::Middle => 'Middle banner',
            self::Footer => 'Footer banner',
            self::OfferZone => 'Offer Zone banner',
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
