<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Rizky Pratama',
                'email' => 'admin@asri.co.id',
                'nik' => 'ADM001',
                'site' => 'O99',
                'role' => 'admin',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'teknisi@asri.co.id',
                'nik' => 'IT001',
                'site' => 'O99',
                'role' => 'teknisi',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'teknisi2@asri.co.id',
                'nik' => 'IT002',
                'site' => 'O99',
                'role' => 'teknisi',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'user@asri.co.id',
                'nik' => 'USR001',
                'site' => 'A01',
                'role' => 'pengguna',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'user2@asri.co.id',
                'nik' => 'USR002',
                'site' => 'F01',
                'role' => 'pengguna',
            ],
            [
                'name' => 'Maya Indah',
                'email' => 'user3@asri.co.id',
                'nik' => 'USR003',
                'site' => 'A02',
                'role' => 'pengguna',
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'supervisor@asri.co.id',
                'nik' => 'SUP001',
                'site' => 'O99',
                'role' => 'supervisor_it',
            ],
            [
                'name' => 'Dewi Kartika',
                'email' => 'manager@asri.co.id',
                'nik' => 'MGR001',
                'site' => 'O99',
                'role' => 'manager_it',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $site = $userData['site'] ?? null;
            unset($userData['site']);

            Employee::updateOrCreate(
                ['nik' => $userData['nik']],
                ['name' => $userData['name'], 'site' => $site]
            );

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                    'theme_preference' => 'light',
                ])
            );

            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }

            Employee::where('nik', $user->nik)->update([
                'email' => $user->email,
                'akun_login' => 'Connect',
                'status' => Employee::STATUS_ACTIVE,
            ]);
        }
    }
}
