<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('section_title', 80)->default('Offer Zone');
            $table->string('section_subtitle', 160)->nullable();
            $table->string('eyebrow', 40)->default('Offer Zone');
            $table->string('theme', 32)->default('classic');
            $table->boolean('countdown_enabled')->default(false);
            $table->timestamp('countdown_ends_at')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        DB::table('offer_page_settings')->insert([
            'section_title' => 'Offer Zone',
            'section_subtitle' => 'Limited time offers just for you!',
            'eyebrow' => 'Offer Zone',
            'theme' => 'classic',
            'countdown_enabled' => false,
            'countdown_ends_at' => null,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_page_settings');
    }
};
