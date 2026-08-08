<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->boolean('barcode_fisik')->nullable()->after('kondisi_akhir_notes');
        });
    }

    public function down(): void
    {
        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->dropColumn('barcode_fisik');
        });
    }
};
