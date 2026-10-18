<?php

namespace App\Modules\Offer\Enums;

enum OfferTheme: string
{
    case Classic = 'classic';
    case Festival = 'festival';
    case Diwali = 'diwali';
    case Christmas = 'christmas';
    case Summer = 'summer';
    case Holi = 'holi';

    public function label(): string
    {
        return match ($this) {
            self::Classic => 'Classic',
            self::Festival => 'Festival season',
            self::Diwali => 'Diwali',
            self::Christmas => 'Christmas',
            self::Summer => 'Summer',
            self::Holi => 'Holi',
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
