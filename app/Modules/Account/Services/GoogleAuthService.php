<?php

namespace App\Modules\Account\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public function findOrCreateFromGoogle(SocialiteUser $googleUser): User
    {
        $googleId = (string) $googleUser->getId();
        $email = $this->normalizedEmail($googleUser->getEmail());
        $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User'));

        $user = User::query()->where('google_id', $googleId)->first();

        if ($user === null && $email !== null) {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user !== null) {
            $updates = [];

            if ($user->google_id === null) {
                $updates['google_id'] = $googleId;
            }

            if ($user->email === null && $email !== null) {
                $updates['email'] = $email;
            }

            if ($user->email_verified_at === null && $email !== null) {
                $updates['email_verified_at'] = now();
            }

            if ($updates !== []) {
                $user->update($updates);
            }

            return $user->fresh();
        }

        return User::query()->create([
            'name' => $name !== '' ? $name : 'Google User',
            'email' => $email,
            'google_id' => $googleId,
            'mobile' => null,
            'password' => Hash::make(Str::password(40)),
            'email_verified_at' => $email !== null ? now() : null,
            'is_admin' => false,
        ]);
    }

    /**
     * @param  array{
     *     name: string,
     *     mobile: string,
     *     addresses: list<array{
     *         label: string,
     *         full_address: string,
     *         pincode: string,
     *         district: string,
     *         state: string,
     *         is_default?: bool
     *     }>
     * }  $data
     */
    public function completeRegistration(User $user, array $data): User
    {
        $wasIncomplete = ! $user->hasCompletedRegistration();

        return DB::transaction(function () use ($user, $data, $wasIncomplete): User {
            $user->update([
                'name' => $data['name'],
                'mobile' => $data['mobile'],
            ]);

            $user->addresses()->delete();

            $addresses = array_values($data['addresses']);
            $explicitDefault = collect($addresses)->contains(
                fn (array $row): bool => (bool) ($row['is_default'] ?? false),
            );
            $hasDefault = false;

            foreach ($addresses as $index => $address) {
                $isDefault = (bool) ($address['is_default'] ?? false);
                if (! $explicitDefault && $index === 0) {
                    $isDefault = true;
                }
                if ($hasDefault) {
                    $isDefault = false;
                }
                if ($isDefault) {
                    $hasDefault = true;
                }

                $user->addresses()->create([
                    'label' => trim((string) $address['label']),
                    'full_address' => trim((string) $address['full_address']),
                    'pincode' => trim((string) $address['pincode']),
                    'district' => trim((string) $address['district']),
                    'state' => trim((string) $address['state']),
                    'is_default' => $isDefault,
                ]);
            }

            $completed = $user->fresh(['addresses']);

            if ($wasIncomplete) {
                event(new Registered($completed));
            }

            return $completed;
        });
    }

    private function normalizedEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        return $email !== '' ? strtolower($email) : null;
    }
}
