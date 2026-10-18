<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('countdown_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('offer_campaign_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['offer_campaign_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_campaign_product');
        Schema::dropIfExists('offer_campaigns');
    }
};
