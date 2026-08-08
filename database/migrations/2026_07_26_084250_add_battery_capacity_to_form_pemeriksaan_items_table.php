<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_pemeriksaan_items', function (Blueprint $table) {
            $table->integer('full_charge_capacity')->nullable()->after('keterangan');
            $table->integer('design_capacity')->nullable()->after('full_charge_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_pemeriksaan_items', function (Blueprint $table) {
            $table->dropColumn(['full_charge_capacity', 'design_capacity']);
        });
    }
};
