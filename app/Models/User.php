<?php

namespace App\Models;

use App\Modules\Account\Models\UserAddress;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'mobile', 'google_id', 'password', 'is_admin', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class)->orderByDesc('is_default')->orderBy('id');
    }

    public function hasCompletedRegistration(): bool
    {
        if (! filled($this->mobile)) {
            return false;
        }

        if ($this->relationLoaded('addresses')) {
            return $this->addresses->isNotEmpty();
        }

        return $this->addresses()->exists();
    }
}
