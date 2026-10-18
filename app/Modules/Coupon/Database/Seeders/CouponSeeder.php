<?php

namespace App\Modules\Coupon\Database\Seeders;

use App\Modules\Coupon\Enums\CouponType;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome gift',
                'type' => CouponType::Percent,
                'value' => 10,
                'max_discount_amount' => 150,
                'min_order_amount' => 299,
                'max_order_amount' => null,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
                'total_usage_limit' => 500,
                'per_user_usage_limit' => 1,
                'first_order_only' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SAVE20',
                'name' => 'Cart saver',
                'type' => CouponType::Percent,
                'value' => 20,
                'max_discount_amount' => 250,
                'min_order_amount' => 799,
                'max_order_amount' => null,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(2),
                'total_usage_limit' => 200,
                'per_user_usage_limit' => 2,
                'first_order_only' => false,
                'is_active' => true,
            ],
            [
                'code' => 'FLAT100',
                'name' => 'Flat ₹100 off',
                'type' => CouponType::Fixed,
                'value' => 100,
                'max_discount_amount' => null,
                'min_order_amount' => 999,
                'max_order_amount' => null,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'total_usage_limit' => 300,
                'per_user_usage_limit' => 3,
                'first_order_only' => false,
                'is_active' => true,
            ],
            [
                'code' => 'FLAT50',
                'name' => 'Quick ₹50 off',
                'type' => CouponType::Fixed,
                'value' => 50,
                'max_discount_amount' => null,
                'min_order_amount' => 499,
                'max_order_amount' => null,
                'starts_at' => null,
                'ends_at' => null,
                'total_usage_limit' => null,
                'per_user_usage_limit' => null,
                'first_order_only' => false,
                'is_active' => true,
            ],
            [
                'code' => 'MEGA30',
                'name' => 'Mega percent deal',
                'type' => CouponType::Percent,
                'value' => 30,
                'max_discount_amount' => 400,
                'min_order_amount' => 1499,
                'max_order_amount' => 15000,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addWeeks(6),
                'total_usage_limit' => 100,
                'per_user_usage_limit' => 1,
                'first_order_only' => false,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                $coupon,
            );
        }

        $productIds = Product::query()
            ->where('is_unlaunched', false)
            ->orderBy('id')
            ->limit(3)
            ->pluck('id')
            ->all();

        if ($productIds === []) {
            return;
        }

        $productCoupon = Coupon::query()->updateOrCreate(
            ['code' => 'PICK3'],
            [
                'name' => 'Selected products deal',
                'type' => CouponType::Percent,
                'value' => 15,
                'max_discount_amount' => 200,
                'min_order_amount' => null,
                'max_order_amount' => null,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(2),
                'total_usage_limit' => 150,
                'per_user_usage_limit' => 2,
                'first_order_only' => false,
                'is_active' => true,
            ],
        );

        $productCoupon->products()->sync($productIds);
    }
}
