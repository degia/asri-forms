<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employee dihapus: lepaskan email & lepas akun login yang menautkannya
        $deleted = DB::table('employees')->whereNotNull('deleted_at')->get(['nik', 'email']);

        foreach ($deleted as $employee) {
            if ($employee->email !== null) {
                DB::table('employees')->where('nik', $employee->nik)->update(['email' => null]);
            }

            DB::table('users')->where('nik', $employee->nik)->update(['nik' => null]);
        }

        // 2. Employee yang akun loginnya sudah dihapus (soft delete): bersihkan email
        $trashedUserEmails = DB::table('users')->whereNotNull('deleted_at')->pluck('email');

        if ($trashedUserEmails->isNotEmpty()) {
            DB::table('employees')
                ->whereIn('email', $trashedUserEmails)
                ->update(['email' => null]);
        }

        // 3. Employee dengan email yang tidak terdaftar di users: bersihkan (FK aman)
        $userEmails = DB::table('users')->pluck('email');

        if ($userEmails->isNotEmpty()) {
            DB::table('employees')
                ->whereNotNull('email')
                ->whereNotIn('email', $userEmails)
                ->update(['email' => null]);
        }
    }

    public function down(): void
    {
    }
};
