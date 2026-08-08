<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE form_perawatan MODIFY COLUMN kondisi_akhir ENUM('good_normal', 'caution_poor', 'good', 'fair', 'critical', 'poor') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE form_perawatan MODIFY COLUMN kondisi_akhir ENUM('good_normal', 'caution_poor') NULL");
    }
};
