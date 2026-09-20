<?php

namespace App\Modules\Admin\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'password' => 'Admin@123',
                'email_verified_at' => now(),
                'is_admin' => true,
            ],
        );
    }
}
