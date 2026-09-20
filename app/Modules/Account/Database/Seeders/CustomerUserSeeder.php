<?php

namespace App\Modules\Account\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@mail.com'],
            [
                'name' => 'Demo Customer',
                'mobile' => '7708782197',
                'password' => 'Admin@123',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
        );

        $customer->addresses()->delete();

        $customer->addresses()->create([
            'label' => 'Home',
            'full_address' => '14, Lakeview Apartments, MG Road',
            'pincode' => '560001',
            'district' => 'Bengaluru Urban',
            'state' => 'Karnataka',
            'is_default' => true,
        ]);

        $customer->addresses()->create([
            'label' => 'Work',
            'full_address' => 'Floor 3, Tech Park, Outer Ring Road',
            'pincode' => '560103',
            'district' => 'Bengaluru Urban',
            'state' => 'Karnataka',
            'is_default' => false,
        ]);
    }
}
