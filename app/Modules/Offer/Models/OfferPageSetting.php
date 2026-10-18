<?php

namespace App\Modules\Offer\Models;

use App\Modules\Offer\Enums\OfferTheme;
use Illuminate\Database\Eloquent\Model;

class OfferPageSetting extends Model
{
    protected $fillable = [
        'section_title',
        'section_subtitle',
        'eyebrow',
        'theme',
        'countdown_enabled',
        'countdown_ends_at',
        'is_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theme' => OfferTheme::class,
            'countdown_enabled' => 'boolean',
            'countdown_ends_at' => 'datetime',
            'is_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting !== null) {
            return $setting;
        }

        return static::query()->create([
            'section_title' => 'Offer Zone',
            'section_subtitle' => 'Limited time offers just for you!',
            'eyebrow' => 'Offer Zone',
            'theme' => OfferTheme::Classic,
            'countdown_enabled' => false,
            'is_enabled' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'section_title' => $this->section_title,
            'section_subtitle' => $this->section_subtitle ?? '',
            'eyebrow' => $this->eyebrow,
            'theme' => $this->theme->value,
            'countdown_enabled' => $this->countdown_enabled,
            'countdown_ends_at' => $this->countdown_ends_at?->format('Y-m-d\TH:i'),
            'is_enabled' => $this->is_enabled,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        $countdown = $this->countdown_enabled && $this->countdown_ends_at !== null;

        return [
            'title' => $this->section_title,
            'subtitle' => $this->section_subtitle,
            'eyebrow' => $this->eyebrow,
            'theme' => $this->theme->value,
            'countdownEnabled' => $countdown,
            'endsAt' => $countdown ? $this->countdown_ends_at?->toIso8601String() : null,
            'enabled' => $this->is_enabled,
        ];
    }
}
