<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restrukturisasi tabel users & employees:
     *  - users.email  -> primary key, users.nik  -> FK ke employees.nik
     *  - employees.nik -> primary key, employees.email -> FK ke users.email
     *  - status baru: users ('Enable'/'Disable'), employees ('Active'/'Resigned')
     *  - kolom akun_login & date_resign pada employees
     *  - buang kolom lama (department/business_unit/site/no_telepon/employee_id,
     *    id numeric, pengguna_id & assigned_user_id legacy)
     *
     * Destructive: restore dari backup jika terjadi masalah.
     */
    public function up(): void
    {
        // Phase 1: kolom baru yang dibutuhkan proses data
        DB::statement("ALTER TABLE employees ADD COLUMN akun_login ENUM('Connect','No Access') NULL AFTER email");
        DB::statement('ALTER TABLE employees ADD COLUMN date_resign DATE NULL AFTER akun_login');

        $this->cleanupData();

        $this->restructureEmployees();

        $this->restructureUsers();

        $this->addRelations();
    }

    private function cleanupData(): void
    {
        // 1. Hapus akun test (@example.*) dan role map miliknya
        $testUserIds = DB::table('users')->where('email', 'like', '%@example.%')->pluck('id');
        if ($testUserIds->isNotEmpty()) {
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_id', $testUserIds)
                ->delete();
            DB::table('users')->whereIn('id', $testUserIds)->delete();
        }

        // 2. Bersihkan role map yang mengacu ke user id yang sudah tidak ada
        DB::table('model_has_roles')
            ->where('model_type', 'App\\Models\\User')
            ->whereNotIn('model_id', DB::table('users')->pluck('id'))
            ->delete();

        // 3. Buat employee untuk user yang punya NIK tapi belum punya employee
        $existingNiks = DB::table('employees')->whereNotNull('nik')->where('nik', '<>', '')->pluck('nik');
        $usersWithoutEmployee = DB::table('users')
            ->whereNotNull('nik')->where('nik', '<>', '')
            ->whereNotIn('nik', $existingNiks)
            ->get();

        foreach ($usersWithoutEmployee as $user) {
            DB::table('employees')->insert([
                'name' => $user->name,
                'nik' => $user->nik,
                'email' => $user->email,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Isi NIK placeholder untuk employee tanpa NIK (unik, mudah dikoreksi nanti)
        $usedNiks = DB::table('employees')->whereNotNull('nik')->where('nik', '<>', '')->pluck('nik')->flip();
        $counter = 1;
        foreach (DB::table('employees')->orderBy('id')->get() as $employee) {
            if (empty($employee->nik)) {
                $candidate = 'NIK-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
                while ($usedNiks->has($candidate)) {
                    $counter++;
                    $candidate = 'NIK-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
                }
                DB::table('employees')->where('id', $employee->id)->update(['nik' => $candidate]);
                $usedNiks->put($candidate, true);
                $counter++;
            }
        }

        // 5. Email employee yang tidak punya akun user -> null (syarat FK employees.email -> users.email)
        $userEmails = DB::table('users')->pluck('email');
        DB::table('employees')
            ->whereNotNull('email')
            ->whereNotIn('email', $userEmails)
            ->update(['email' => null]);

        // 6. Backfill users.nik dari relasi lama employees.employee_id (sebelum kolom di-drop)
        $rows = DB::table('users')
            ->whereNotNull('employee_id')
            ->get();
        foreach ($rows as $user) {
            $nik = DB::table('employees')->where('id', $user->employee_id)->value('nik');
            if ($nik) {
                DB::table('users')->where('id', $user->id)->update(['nik' => $nik]);
            }
        }

        // 7. Pemetaan nilai status ke enum baru
        DB::table('users')->where('status', 'active')->update(['status' => 'Enable']);
        DB::table('users')->where('status', 'resigned')->update(['status' => 'Disable']);
        DB::table('employees')->where('status', 'active')->update(['status' => 'Active']);
        DB::table('employees')->where('status', 'resigned')->update(['status' => 'Resigned']);

        // 8. Normalisasi employees.site agar sesuai dengan sites.id_site
        DB::table('employees')->where('site', 'PIK Avenue')->update(['site' => 'M01']);
        DB::table('employees')->where('site', 'Head Office')->update(['site' => 'O99']);
        $validSiteIds = DB::table('sites')->pluck('id_site');
        DB::table('employees')->whereNotNull('site')->whereNotIn('site', $validSiteIds)->update(['site' => null]);

        // 9. Nilai awal akun_login
        foreach (DB::table('employees')->get() as $employee) {
            $hasActiveUser = DB::table('users')
                ->where('nik', $employee->nik)
                ->where('status', 'Enable')
                ->exists();

            DB::table('employees')->where('id', $employee->id)->update([
                'akun_login' => $hasActiveUser ? 'Connect' : 'No Access',
            ]);
        }
    }

    private function restructureEmployees(): void
    {
        // Lepas FK lama yang mengacu ke employees.id
        DB::statement('ALTER TABLE users DROP FOREIGN KEY users_employee_id_foreign');
        DB::statement('ALTER TABLE assets DROP FOREIGN KEY assets_assigned_employee_id_foreign');
        DB::statement('ALTER TABLE form_pemeriksaan DROP FOREIGN KEY form_pemeriksaan_pengguna_employee_id_foreign');
        DB::statement('ALTER TABLE form_perawatan DROP FOREIGN KEY form_perawatan_pengguna_employee_id_foreign');
        DB::statement('ALTER TABLE form_pengembalian DROP FOREIGN KEY form_pengembalian_pengguna_employee_id_foreign');

        // Ubah kolom FK ke string (nik)
        DB::statement('ALTER TABLE assets MODIFY COLUMN assigned_employee_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE form_pemeriksaan MODIFY COLUMN pengguna_employee_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE form_perawatan MODIFY COLUMN pengguna_employee_id VARCHAR(50) NULL');
        DB::statement('ALTER TABLE form_pengembalian MODIFY COLUMN pengguna_employee_id VARCHAR(50) NULL');

        // Konversi data: id employee -> nik
        DB::statement('UPDATE assets a JOIN employees e ON e.id = a.assigned_employee_id SET a.assigned_employee_id = e.nik');
        DB::statement('UPDATE form_pemeriksaan f JOIN employees e ON e.id = f.pengguna_employee_id SET f.pengguna_employee_id = e.nik');
        DB::statement('UPDATE form_perawatan f JOIN employees e ON e.id = f.pengguna_employee_id SET f.pengguna_employee_id = e.nik');
        DB::statement('UPDATE form_pengembalian f JOIN employees e ON e.id = f.pengguna_employee_id SET f.pengguna_employee_id = e.nik');

        // nik menjadi primary key
        DB::statement('ALTER TABLE employees MODIFY COLUMN nik VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE employees MODIFY COLUMN status ENUM(\'Active\',\'Resigned\') NOT NULL DEFAULT \'Active\'');
        DB::statement('ALTER TABLE employees DROP PRIMARY KEY, DROP COLUMN id, ADD PRIMARY KEY (nik)');
        DB::statement('ALTER TABLE employees DROP INDEX employees_nik_index');

        // Hapus kolom yang tidak lagi dipakai
        DB::statement('ALTER TABLE employees DROP COLUMN department');
        DB::statement('ALTER TABLE employees DROP COLUMN business_unit');

        // Email employee unik (nullable)
        DB::statement('ALTER TABLE employees ADD UNIQUE INDEX employees_email_unique (email)');
    }

    private function restructureUsers(): void
    {
        // Lepas FK lama yang mengacu ke users.id
        DB::statement('ALTER TABLE activity_logs DROP FOREIGN KEY activity_logs_user_id_foreign');
        DB::statement('ALTER TABLE form_pemeriksaan DROP FOREIGN KEY form_pemeriksaan_user_id_foreign');
        if (Schema::hasColumn('form_pemeriksaan', 'pengguna_id')) {
            DB::statement('ALTER TABLE form_pemeriksaan DROP FOREIGN KEY form_pemeriksaan_pengguna_id_foreign');
        }
        DB::statement('ALTER TABLE form_perawatan DROP FOREIGN KEY form_perawatan_user_id_foreign');
        if (Schema::hasColumn('form_perawatan', 'pengguna_id')) {
            DB::statement('ALTER TABLE form_perawatan DROP FOREIGN KEY form_perawatan_pengguna_id_foreign');
        }
        DB::statement('ALTER TABLE form_pengembalian DROP FOREIGN KEY form_pengembalian_teknisi_id_foreign');
        DB::statement('ALTER TABLE form_approvals DROP FOREIGN KEY form_approvals_user_id_foreign');
        if (Schema::hasColumn('assets', 'assigned_user_id')) {
            DB::statement('ALTER TABLE assets DROP FOREIGN KEY assets_assigned_user_id_foreign');
        }

        // status -> enum Enable/Disable
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('Enable','Disable') NOT NULL DEFAULT 'Enable'");

        // Ubah kolom FK ke string (email)
        DB::statement('ALTER TABLE activity_logs MODIFY COLUMN user_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE form_pemeriksaan MODIFY COLUMN user_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE form_perawatan MODIFY COLUMN user_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE form_pengembalian MODIFY COLUMN teknisi_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE form_approvals MODIFY COLUMN user_id VARCHAR(255) NULL');
        if (Schema::hasColumn('assets', 'assigned_user_id')) {
            DB::statement('ALTER TABLE assets MODIFY COLUMN assigned_user_id VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('form_pemeriksaan', 'pengguna_id')) {
            DB::statement('ALTER TABLE form_pemeriksaan MODIFY COLUMN pengguna_id VARCHAR(255) NULL');
        }
        if (Schema::hasColumn('form_perawatan', 'pengguna_id')) {
            DB::statement('ALTER TABLE form_perawatan MODIFY COLUMN pengguna_id VARCHAR(255) NULL');
        }
        DB::statement('ALTER TABLE sessions MODIFY COLUMN user_id VARCHAR(255) NULL');
        DB::statement('ALTER TABLE model_has_roles MODIFY COLUMN model_id VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE model_has_permissions MODIFY COLUMN model_id VARCHAR(255) NOT NULL');

        // Konversi data: id user -> email
        DB::statement('UPDATE activity_logs al JOIN users u ON u.id = al.user_id SET al.user_id = u.email WHERE al.user_id IS NOT NULL');
        DB::statement('UPDATE form_pemeriksaan f JOIN users u ON u.id = f.user_id SET f.user_id = u.email');
        DB::statement('UPDATE form_perawatan f JOIN users u ON u.id = f.user_id SET f.user_id = u.email');
        DB::statement('UPDATE form_pengembalian f JOIN users u ON u.id = f.teknisi_id SET f.teknisi_id = u.email');
        DB::statement('UPDATE form_approvals fa JOIN users u ON u.id = fa.user_id SET fa.user_id = u.email WHERE fa.user_id IS NOT NULL');
        if (Schema::hasColumn('assets', 'assigned_user_id')) {
            DB::statement('UPDATE assets a JOIN users u ON u.id = a.assigned_user_id SET a.assigned_user_id = u.email WHERE a.assigned_user_id IS NOT NULL');
        }
        if (Schema::hasColumn('form_pemeriksaan', 'pengguna_id')) {
            DB::statement('UPDATE form_pemeriksaan f JOIN users u ON u.id = f.pengguna_id SET f.pengguna_id = u.email WHERE f.pengguna_id IS NOT NULL');
        }
        if (Schema::hasColumn('form_perawatan', 'pengguna_id')) {
            DB::statement('UPDATE form_perawatan f JOIN users u ON u.id = f.pengguna_id SET f.pengguna_id = u.email WHERE f.pengguna_id IS NOT NULL');
        }
        DB::statement('UPDATE sessions s JOIN users u ON u.id = s.user_id SET s.user_id = u.email WHERE s.user_id IS NOT NULL');
        DB::statement("UPDATE model_has_roles mh JOIN users u ON u.id = mh.model_id SET mh.model_id = u.email WHERE mh.model_type = 'App\\\\Models\\\\User'");

        // Hapus kolom legacy & kolom yang tidak dipakai
        if (Schema::hasColumn('assets', 'assigned_user_id')) {
            DB::statement('ALTER TABLE assets DROP COLUMN assigned_user_id');
        }
        if (Schema::hasColumn('form_pemeriksaan', 'pengguna_id')) {
            DB::statement('ALTER TABLE form_pemeriksaan DROP COLUMN pengguna_id');
        }
        if (Schema::hasColumn('form_perawatan', 'pengguna_id')) {
            DB::statement('ALTER TABLE form_perawatan DROP COLUMN pengguna_id');
        }
        DB::statement('ALTER TABLE users DROP COLUMN employee_id');
        DB::statement('ALTER TABLE users DROP COLUMN department');
        DB::statement('ALTER TABLE users DROP COLUMN business_unit');
        DB::statement('ALTER TABLE users DROP COLUMN site');
        DB::statement('ALTER TABLE users DROP COLUMN no_telepon');

        // email menjadi primary key
        DB::statement('ALTER TABLE users DROP PRIMARY KEY, DROP COLUMN id, ADD PRIMARY KEY (email)');
        DB::statement('ALTER TABLE users DROP INDEX users_email_unique');
    }

    private function addRelations(): void
    {
        // Relasi silang users <-> employees
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_nik_foreign FOREIGN KEY (nik) REFERENCES employees(nik) ON DELETE SET NULL');
        DB::statement('ALTER TABLE employees ADD CONSTRAINT employees_email_foreign FOREIGN KEY (email) REFERENCES users(email) ON DELETE SET NULL');
        DB::statement('ALTER TABLE employees ADD CONSTRAINT employees_site_foreign FOREIGN KEY (site) REFERENCES sites(id_site) ON DELETE SET NULL');

        // FK yang mengacu ke users.email
        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(email) ON DELETE SET NULL');
        DB::statement('ALTER TABLE form_pemeriksaan ADD CONSTRAINT form_pemeriksaan_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(email) ON DELETE RESTRICT');
        DB::statement('ALTER TABLE form_perawatan ADD CONSTRAINT form_perawatan_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(email) ON DELETE RESTRICT');
        DB::statement('ALTER TABLE form_pengembalian ADD CONSTRAINT form_pengembalian_teknisi_id_foreign FOREIGN KEY (teknisi_id) REFERENCES users(email) ON DELETE CASCADE');
        DB::statement('ALTER TABLE form_approvals ADD CONSTRAINT form_approvals_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(email) ON DELETE RESTRICT');

        // FK yang mengacu ke employees.nik
        DB::statement('ALTER TABLE assets ADD CONSTRAINT assets_assigned_employee_id_foreign FOREIGN KEY (assigned_employee_id) REFERENCES employees(nik) ON DELETE SET NULL');
        DB::statement('ALTER TABLE form_pemeriksaan ADD CONSTRAINT form_pemeriksaan_pengguna_employee_id_foreign FOREIGN KEY (pengguna_employee_id) REFERENCES employees(nik) ON DELETE SET NULL');
        DB::statement('ALTER TABLE form_perawatan ADD CONSTRAINT form_perawatan_pengguna_employee_id_foreign FOREIGN KEY (pengguna_employee_id) REFERENCES employees(nik) ON DELETE SET NULL');
        DB::statement('ALTER TABLE form_pengembalian ADD CONSTRAINT form_pengembalian_pengguna_employee_id_foreign FOREIGN KEY (pengguna_employee_id) REFERENCES employees(nik) ON DELETE SET NULL');
    }

    public function down(): void
    {
        throw new RuntimeException('Migrasi restrukturisasi users/employees bersifat destruktif. Restore dari backup untuk kembali.');
    }
};
