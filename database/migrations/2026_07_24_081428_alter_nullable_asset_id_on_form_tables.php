<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()->change();
            $table->enum('kondisi', ['baru', 'lama'])->nullable()->change();
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()->change();
            $table->enum('kondisi_akhir', ['good_normal', 'caution_poor'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable(false)->change();
            $table->enum('kondisi', ['baru', 'lama'])->nullable(false)->change();
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable(false)->change();
            $table->enum('kondisi_akhir', ['good_normal', 'caution_poor'])->nullable(false)->change();
        });
    }
};
