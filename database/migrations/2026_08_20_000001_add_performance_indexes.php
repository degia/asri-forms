<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- assets ---
        Schema::table('assets', function (Blueprint $table) {
            $table->index('operating_unit');
            $table->index('status');
            $table->index('assigned_employee_id');
            $table->index('site_location_asset');
        });

        // --- form_perawatan ---
        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->index('submitted_at');
            $table->index('status');
            $table->index('asset_id');
            $table->index('site_location');
            $table->index('pengguna_employee_id');
        });

        // --- form_pemeriksaan ---
        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->index('submitted_at');
            $table->index('status');
            $table->index('asset_id');
            $table->index('site_location');
            $table->index('pengguna_employee_id');
        });

        // --- employees ---
        Schema::table('employees', function (Blueprint $table) {
            $table->index('site');
            $table->index('status');
            $table->index('directorate_id');
            $table->index('divisi_id');
            $table->index('departement_id');
            $table->index('sub_departement_id');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('assets_operating_unit_index');
            $table->dropIndex('assets_status_index');
            $table->dropIndex('assets_assigned_employee_id_index');
            $table->dropIndex('assets_site_location_asset_index');
        });

        Schema::table('form_perawatan', function (Blueprint $table) {
            $table->dropIndex('form_perawatan_submitted_at_index');
            $table->dropIndex('form_perawatan_status_index');
            $table->dropIndex('form_perawatan_asset_id_index');
            $table->dropIndex('form_perawatan_site_location_index');
            $table->dropIndex('form_perawatan_pengguna_employee_id_index');
        });

        Schema::table('form_pemeriksaan', function (Blueprint $table) {
            $table->dropIndex('form_pemeriksaan_submitted_at_index');
            $table->dropIndex('form_pemeriksaan_status_index');
            $table->dropIndex('form_pemeriksaan_asset_id_index');
            $table->dropIndex('form_pemeriksaan_site_location_index');
            $table->dropIndex('form_pemeriksaan_pengguna_employee_id_index');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_site_index');
            $table->dropIndex('employees_status_index');
            $table->dropIndex('employees_directorate_id_index');
            $table->dropIndex('employees_divisi_id_index');
            $table->dropIndex('employees_departement_id_index');
            $table->dropIndex('employees_sub_departement_id_index');
        });
    }
};
