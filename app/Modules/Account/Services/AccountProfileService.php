<?php

namespace App\Modules\Account\Services;

use App\Models\User;
use App\Modules\Account\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class AccountProfileService
{
    /**
     * @param  array{name: string, email: ?string, mobile: string}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'],
        ]);
        $user->save();

        return $user->refresh();
    }

    /**
     * @param  array{
     *     label: string,
     *     full_address: string,
     *     pincode: string,
     *     district: string,
     *     state: string,
     *     is_default?: bool
     * }  $data
     */
    public function storeAddress(User $user, array $data): UserAddress
    {
        return DB::transaction(function () use ($user, $data): UserAddress {
            $makeDefault = (bool) ($data['is_default'] ?? false) || $user->addresses()->count() === 0;

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                'label' => $data['label'],
                'full_address' => $data['full_address'],
                'pincode' => $data['pincode'],
                'district' => $data['district'],
                'state' => $data['state'],
                'is_default' => $makeDefault,
            ]);
        });
    }

    /**
     * @param  array{
     *     label: string,
     *     full_address: string,
     *     pincode: string,
     *     district: string,
     *     state: string,
     *     is_default?: bool
     * }  $data
     */
    public function updateAddress(User $user, UserAddress $address, array $data): UserAddress
    {
        abort_unless((int) $address->user_id === (int) $user->id, 403);

        return DB::transaction(function () use ($user, $address, $data): UserAddress {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }

            $address->update([
                'label' => $data['label'],
                'full_address' => $data['full_address'],
                'pincode' => $data['pincode'],
                'district' => $data['district'],
                'state' => $data['state'],
                'is_default' => $makeDefault,
            ]);

            if (! $user->addresses()->where('is_default', true)->exists()) {
                $address->update(['is_default' => true]);
            }

            return $address->refresh();
        });
    }

    public function deleteAddress(User $user, UserAddress $address): void
    {
        abort_unless((int) $address->user_id === (int) $user->id, 403);

        DB::transaction(function () use ($user, $address): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $user->addresses()->orderBy('id')->first()?->update(['is_default' => true]);
            }
        });
    }
}
