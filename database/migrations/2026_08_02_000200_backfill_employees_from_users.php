<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $userIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'pengguna')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id')
            ->merge(DB::table('form_pemeriksaan')->pluck('pengguna_id'))
            ->merge(DB::table('form_perawatan')->pluck('pengguna_id'))
            ->merge(DB::table('form_pengembalian')->pluck('pengguna_id'))
            ->merge(DB::table('assets')->pluck('assigned_user_id'))
            ->filter()
            ->unique()
            ->values();

        $usedNiks = [];

        foreach ($userIds as $uid) {
            $user = DB::table('users')->find($uid);

            if (! $user) {
                continue;
            }

            $nik = $user->nik ?: null;

            if ($nik !== null) {
                if (isset($usedNiks[$nik])) {
                    $nik = null;
                } else {
                    $usedNiks[$nik] = true;
                }
            }

            $employeeId = DB::table('employees')->insertGetId([
                'name' => $user->name,
                'nik' => $nik,
                'department' => $user->department ?: null,
                'business_unit' => $user->business_unit ?: null,
                'site' => $user->site ?: null,
                'no_telepon' => $user->no_telepon ?: null,
                'email' => $user->email ?: null,
                'status' => $user->status ?: 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $uid)->update(['employee_id' => $employeeId]);
        }

        DB::statement('UPDATE form_pemeriksaan fp JOIN users u ON u.id = fp.pengguna_id SET fp.pengguna_employee_id = u.employee_id WHERE fp.pengguna_id IS NOT NULL');

        DB::statement('UPDATE form_perawatan fp JOIN users u ON u.id = fp.pengguna_id SET fp.pengguna_employee_id = u.employee_id WHERE fp.pengguna_id IS NOT NULL');

        DB::statement('UPDATE form_pengembalian fp JOIN users u ON u.id = fp.pengguna_id SET fp.pengguna_employee_id = u.employee_id WHERE fp.pengguna_id IS NOT NULL');

        DB::statement('UPDATE assets a JOIN users u ON u.id = a.assigned_user_id SET a.assigned_employee_id = u.employee_id WHERE a.assigned_user_id IS NOT NULL');
    }

    public function down(): void
    {
        DB::table('form_pemeriksaan')->update(['pengguna_employee_id' => null]);
        DB::table('form_perawatan')->update(['pengguna_employee_id' => null]);
        DB::table('form_pengembalian')->update(['pengguna_employee_id' => null]);
        DB::table('assets')->update(['assigned_employee_id' => null]);
        DB::table('users')->update(['employee_id' => null]);
        DB::table('employees')->truncate();
    }
};
