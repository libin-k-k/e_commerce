<?php

namespace App\Modules\Banner\Models;

use App\Modules\Banner\Enums\BannerPosition;
use Illuminate\Database\Eloquent\Model;

class BannerSection extends Model
{
    protected $fillable = [
        'position',
        'is_enabled',
        'show_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => BannerPosition::class,
            'is_enabled' => 'boolean',
            'show_count' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'position' => $this->position->value,
            'position_label' => $this->position->label(),
            'is_enabled' => $this->is_enabled,
            'show_count' => $this->show_count,
        ];
    }
}
