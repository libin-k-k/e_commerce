<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('web_image_path')->nullable()->after('style');
            $table->string('mobile_image_path')->nullable()->after('web_image_path');
        });

        if (Schema::hasColumn('banners', 'image_path')) {
            foreach (DB::table('banners')->orderBy('id')->get() as $banner) {
                DB::table('banners')->where('id', $banner->id)->update([
                    'web_image_path' => $banner->image_path,
                    'mobile_image_path' => $banner->image_path,
                ]);
            }

            Schema::table('banners', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('style');
        });

        foreach (DB::table('banners')->orderBy('id')->get() as $banner) {
            DB::table('banners')->where('id', $banner->id)->update([
                'image_path' => $banner->web_image_path,
            ]);
        }

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['web_image_path', 'mobile_image_path']);
        });
    }
};
