<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_sections', function (Blueprint $table) {
            $table->id();
            $table->string('position')->unique();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('show_count')->default(false);
            $table->timestamps();
        });

        $now = now();

        foreach (['hero', 'middle', 'footer', 'offer_zone', 'product_listing', 'home_deals'] as $position) {
            DB::table('banner_sections')->insert([
                'position' => $position,
                'is_enabled' => true,
                'show_count' => $position === 'hero',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_sections');
    }
};
