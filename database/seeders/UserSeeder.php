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
                'name' => 'Administrator',
                'email' => 'admin@asri.co.id',
                'nik' => 'ADM001',
                'site' => 'O99',
                'role' => 'admin',
            ],
            [
                'name' => 'Technician 1',
                'email' => 'technician1@asri.co.id',
                'nik' => 'TEK001',
                'site' => 'O99',
                'role' => 'teknisi',
            ],
            [
                'name' => 'Technician 2',
                'email' => 'technician2@asri.co.id',
                'nik' => 'TEK002',
                'site' => 'O99',
                'role' => 'teknisi',
            ],
            [
                'name' => 'User 1',
                'email' => 'user1@asri.co.id',
                'nik' => 'USR001',
                'site' => 'A01',
                'role' => 'pengguna',
            ],
            [
                'name' => 'User 2',
                'email' => 'user2@asri.co.id',
                'nik' => 'USR002',
                'site' => 'F01',
                'role' => 'pengguna',
            ],
            [
                'name' => 'User 3',
                'email' => 'user3@asri.co.id',
                'nik' => 'USR003',
                'site' => 'A02',
                'role' => 'pengguna',
            ],
            [
                'name' => 'Supervisor IT',
                'email' => 'supervisor@asri.co.id',
                'nik' => 'SUP001',
                'site' => 'O99',
                'role' => 'supervisor_it',
            ],
            [
                'name' => 'Manager IT',
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

            $employee = Employee::query()->where('nik', $user->nik)->first();

            if ($employee) {
                $employee->update([
                    'email' => $user->email,
                    'akun_login' => 'Connect',
                    'status' => Employee::STATUS_ACTIVE,
                ]);
            }
        }
    }
}
