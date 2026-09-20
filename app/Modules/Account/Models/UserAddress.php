<?php

namespace App\Modules\Account\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'full_address',
        'pincode',
        'district',
        'state',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAccountArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'full_address' => $this->full_address,
            'pincode' => $this->pincode,
            'district' => $this->district,
            'state' => $this->state,
            'isDefault' => $this->is_default,
            'line1' => $this->full_address,
            'line2' => '',
            'city' => $this->district,
        ];
    }
}
