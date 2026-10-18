<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 160)->nullable();
            $table->string('type', 20);
            $table->decimal('value', 10, 2);
            // Required for percent coupons: currency cap on discount amount.
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_order_amount', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('total_usage_limit')->nullable();
            $table->unsignedInteger('per_user_usage_limit')->nullable();
            $table->boolean('first_order_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'product_id']);
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_token', 64)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index(['coupon_id', 'status']);
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupons');
    }
};
