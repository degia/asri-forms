<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // --- Users: cascading delete to forms, approvals, activity_logs ---
        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_user_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_user_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_approvals` DROP FOREIGN KEY `form_approvals_user_id_foreign`');
        DB::statement('ALTER TABLE `form_approvals` ADD CONSTRAINT `form_approvals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `activity_logs` DROP FOREIGN KEY `activity_logs_user_id_foreign`');
        DB::statement('ALTER TABLE `activity_logs` ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE');

        // --- Employees: cascading delete to assets, forms ---
        DB::statement('ALTER TABLE `assets` DROP FOREIGN KEY `assets_assigned_employee_id_foreign`');
        DB::statement('ALTER TABLE `assets` ADD CONSTRAINT `assets_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`nik`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees` (`nik`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees` (`nik`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_pengembalian` DROP FOREIGN KEY `form_pengembalian_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pengembalian` ADD CONSTRAINT `form_pengembalian_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees` (`nik`) ON DELETE CASCADE');

        // --- Sites: cascading delete to employees, assets ---
        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_site_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_site_foreign` FOREIGN KEY (`site`) REFERENCES `sites` (`id_site`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `assets` DROP FOREIGN KEY `assets_operating_unit_foreign`');
        DB::statement('ALTER TABLE `assets` ADD CONSTRAINT `assets_operating_unit_foreign` FOREIGN KEY (`operating_unit`) REFERENCES `sites` (`id_site`) ON DELETE CASCADE');

        // --- Assets: cascading delete to forms ---
        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_asset_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_asset_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE');

        // --- Organization hierarchy: cascading delete to employees ---
        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_directorate_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_directorate_id_foreign` FOREIGN KEY (`directorate_id`) REFERENCES `directorates` (`id`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_divisi_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_divisi_id_foreign` FOREIGN KEY (`divisi_id`) REFERENCES `divisis` (`id`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_departement_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_departement_id_foreign` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_sub_departement_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_sub_departement_id_foreign` FOREIGN KEY (`sub_departement_id`) REFERENCES `sub_departements` (`id`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_position_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE');

        // --- Form PMR/PWT items: cascading delete from parent forms ---
        DB::statement('ALTER TABLE `form_pemeriksaan_items` DROP FOREIGN KEY `form_pemeriksaan_items_template_item_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan_items` ADD CONSTRAINT `form_pemeriksaan_items_template_item_id_foreign` FOREIGN KEY (`template_item_id`) REFERENCES `checklist_template_items` (`id`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_perawatan_items` DROP FOREIGN KEY `form_perawatan_items_template_item_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan_items` ADD CONSTRAINT `form_perawatan_items_template_item_id_foreign` FOREIGN KEY (`template_item_id`) REFERENCES `checklist_template_items` (`id`) ON DELETE CASCADE');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Revert Users FKs to RESTRICT/SET NULL
        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_user_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE RESTRICT');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_user_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE RESTRICT');

        DB::statement('ALTER TABLE `form_approvals` DROP FOREIGN KEY `form_approvals_user_id_foreign`');
        DB::statement('ALTER TABLE `form_approvals` ADD CONSTRAINT `form_approvals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE RESTRICT');

        DB::statement('ALTER TABLE `activity_logs` DROP FOREIGN KEY `activity_logs_user_id_foreign`');
        DB::statement('ALTER TABLE `activity_logs` ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE SET NULL');

        // Revert Employees FKs to SET NULL
        DB::statement('ALTER TABLE `assets` DROP FOREIGN KEY `assets_assigned_employee_id_foreign`');
        DB::statement('ALTER TABLE `assets` ADD CONSTRAINT `assets_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`nik`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees` (`nik`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees` (`nik`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `form_pengembalian` DROP FOREIGN KEY `form_pengembalian_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pengembalian` ADD CONSTRAINT `form_pengembalian_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees` (`nik`) ON DELETE SET NULL');

        // Revert Sites FKs to SET NULL
        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_site_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_site_foreign` FOREIGN KEY (`site`) REFERENCES `sites` (`id_site`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `assets` DROP FOREIGN KEY `assets_operating_unit_foreign`');
        DB::statement('ALTER TABLE `assets` ADD CONSTRAINT `assets_operating_unit_foreign` FOREIGN KEY (`operating_unit`) REFERENCES `sites` (`id_site`) ON DELETE SET NULL');

        // Revert Assets FKs to RESTRICT
        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_asset_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE RESTRICT');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_asset_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE RESTRICT');

        // Revert Org hierarchy FKs to SET NULL
        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_directorate_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_directorate_id_foreign` FOREIGN KEY (`directorate_id`) REFERENCES `directorates` (`id`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_divisi_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_divisi_id_foreign` FOREIGN KEY (`divisi_id`) REFERENCES `divisis` (`id`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_departement_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_departement_id_foreign` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_sub_departement_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_sub_departement_id_foreign` FOREIGN KEY (`sub_departement_id`) REFERENCES `sub_departements` (`id`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `employees` DROP FOREIGN KEY `employees_position_id_foreign`');
        DB::statement('ALTER TABLE `employees` ADD CONSTRAINT `employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL');

        // Revert template items FKs to SET NULL
        DB::statement('ALTER TABLE `form_pemeriksaan_items` DROP FOREIGN KEY `form_pemeriksaan_items_template_item_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan_items` ADD CONSTRAINT `form_pemeriksaan_items_template_item_id_foreign` FOREIGN KEY (`template_item_id`) REFERENCES `checklist_template_items` (`id`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `form_perawatan_items` DROP FOREIGN KEY `form_perawatan_items_template_item_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan_items` ADD CONSTRAINT `form_perawatan_items_template_item_id_foreign` FOREIGN KEY (`template_item_id`) REFERENCES `checklist_template_items` (`id`) ON DELETE SET NULL');

        Schema::enableForeignKeyConstraints();
    }
};
