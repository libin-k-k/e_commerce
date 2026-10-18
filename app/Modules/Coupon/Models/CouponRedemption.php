<?php

namespace App\Modules\Coupon\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponRedemption extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'guest_token',
        'discount_amount',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'coupon_id' => 'integer',
            'user_id' => 'integer',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
