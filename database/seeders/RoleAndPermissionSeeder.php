<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'admin' => [
                'manage-users',
                'manage-assets',
                'manage-checklist-templates',
                'view-all-forms',
                'view-reports',
                'create-pemeriksaan',
                'create-perawatan',
                'approve-diketahui',
                'approve-disetujui',
            ],
            'teknisi' => [
                'create-pemeriksaan',
                'create-perawatan',
                'view-own-forms',
            ],
            'pengguna' => [
                'approve-diketahui',
                'view-assigned-forms',
            ],
            'supervisor_it' => [
                'approve-diketahui',
                'approve-disetujui',
                'view-all-forms',
                'view-reports',
            ],
            'manager_it' => [
                'approve-diketahui',
                'approve-disetujui',
                'view-all-forms',
                'view-reports',
            ],
        ];

        $allPermissions = collect($roles)->flatten()->unique()->values();

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
            $role->syncPermissions($permissions);
        }
    }
}
