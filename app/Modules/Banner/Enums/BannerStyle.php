<?php

namespace App\Modules\Banner\Enums;

enum BannerStyle: string
{
    case DarkSplit = 'dark_split';
    case Cinematic = 'cinematic';
    case Gradient = 'gradient';

    public function label(): string
    {
        return match ($this) {
            self::DarkSplit => 'Dark split',
            self::Cinematic => 'Cinematic',
            self::Gradient => 'Gradient',
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
