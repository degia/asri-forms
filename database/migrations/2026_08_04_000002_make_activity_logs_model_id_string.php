<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * model_id dapat berupa id numerik (form/asset) maupun string
     * (email untuk User, nik untuk Employee, id_site untuk Site).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE activity_logs MODIFY COLUMN model_id VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE activity_logs MODIFY COLUMN model_id BIGINT UNSIGNED NULL');
    }
};
