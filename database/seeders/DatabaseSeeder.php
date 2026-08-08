<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            ChecklistTemplateSeeder::class,
            SiteSeeder::class,
            UserSeeder::class,
            AssetSeeder::class,
            FormPemeriksaanSeeder::class,
            FormPerawatanSeeder::class,
            FormApprovalSeeder::class,
        ]);
    }
}
