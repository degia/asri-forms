<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->string('site_location')->nullable()->after('asset_id');
            $table->string('location_detail')->nullable()->after('site_location');
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->string('site_location')->nullable()->after('asset_id');
            $table->string('location_detail')->nullable()->after('site_location');
        });
    }

    public function down(): void
    {
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->dropColumn(['site_location', 'location_detail']);
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->dropColumn(['site_location', 'location_detail']);
        });
    }
};
