<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NIK pada employees dapat berubah: semua tabel yang merujuk
     * employees.nik ikut ter-update otomatis (ON UPDATE CASCADE),
     * dengan aturan ON DELETE tetap sama seperti sebelumnya.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // users.nik -> employees.nik (ON DELETE SET NULL)
        DB::statement('ALTER TABLE `users` DROP FOREIGN KEY `users_nik_foreign`');
        DB::statement('ALTER TABLE `users` ADD CONSTRAINT `users_nik_foreign` FOREIGN KEY (`nik`) REFERENCES `employees`(`nik`) ON DELETE SET NULL ON UPDATE CASCADE');

        // Tabel-tabel berikut: ON DELETE CASCADE
        DB::statement('ALTER TABLE `assets` DROP FOREIGN KEY `assets_assigned_employee_id_foreign`');
        DB::statement('ALTER TABLE `assets` ADD CONSTRAINT `assets_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE ON UPDATE CASCADE');

        DB::statement('ALTER TABLE `form_pengembalian` DROP FOREIGN KEY `form_pengembalian_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pengembalian` ADD CONSTRAINT `form_pengembalian_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE ON UPDATE CASCADE');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('ALTER TABLE `users` DROP FOREIGN KEY `users_nik_foreign`');
        DB::statement('ALTER TABLE `users` ADD CONSTRAINT `users_nik_foreign` FOREIGN KEY (`nik`) REFERENCES `employees`(`nik`) ON DELETE SET NULL');

        DB::statement('ALTER TABLE `assets` DROP FOREIGN KEY `assets_assigned_employee_id_foreign`');
        DB::statement('ALTER TABLE `assets` ADD CONSTRAINT `assets_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_pemeriksaan` DROP FOREIGN KEY `form_pemeriksaan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pemeriksaan` ADD CONSTRAINT `form_pemeriksaan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_perawatan` DROP FOREIGN KEY `form_perawatan_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_perawatan` ADD CONSTRAINT `form_perawatan_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE');

        DB::statement('ALTER TABLE `form_pengembalian` DROP FOREIGN KEY `form_pengembalian_pengguna_employee_id_foreign`');
        DB::statement('ALTER TABLE `form_pengembalian` ADD CONSTRAINT `form_pengembalian_pengguna_employee_id_foreign` FOREIGN KEY (`pengguna_employee_id`) REFERENCES `employees`(`nik`) ON DELETE CASCADE');

        Schema::enableForeignKeyConstraints();
    }
};
