<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            // Base / default pricing used when a product has no variants,
            // and as defaults when generating the size x color matrix.
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            // Aggregated stock (sum of variant stocks when variants exist).
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->string('main_image_path')->nullable();
            $table->boolean('is_unlaunched')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_unlaunched', 'sort_order']);
            $table->index(['category_id', 'subcategory_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Option axes (what the shopper picks).
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'name']);
        });

        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->string('hex', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'name']);
        });

        // Sellable SKUs: one row per size/color combination (or size-only / color-only).
        // Each row owns its own price, sale price, and quantity.
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_size_id')->nullable()->constrained('product_sizes')->cascadeOnDelete();
            $table->foreignId('product_color_id')->nullable()->constrained('product_colors')->cascadeOnDelete();
            // Stable unique key so NULL size/color rows stay unique in MySQL.
            // Format: "s:{sizeId|0}|c:{colorId|0}"
            $table->string('combo_key', 64);
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'combo_key'], 'product_variants_combo_unique');
            $table->unique('sku', 'product_variants_sku_unique');
            $table->index(
                ['product_id', 'product_size_id', 'product_color_id'],
                'product_variants_lookup_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
