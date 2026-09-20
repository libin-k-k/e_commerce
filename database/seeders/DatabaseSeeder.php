<?php

namespace Database\Seeders;

use App\Modules\Account\Database\Seeders\CustomerUserSeeder;
use App\Modules\Admin\Database\Seeders\AdminUserSeeder;
use App\Modules\Banner\Database\Seeders\BannerSeeder;
use App\Modules\Category\Database\Seeders\CategorySeeder;
use App\Modules\Product\Database\Seeders\ProductSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CustomerUserSeeder::class,
            BannerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
