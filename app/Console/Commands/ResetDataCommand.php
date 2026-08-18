<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDataCommand extends Command
{
    protected $signature = 'app:reset-data {--force : Skip confirmation}';
    protected $description = 'Full factory reset: truncate all seeded tables and re-seed from scratch';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Ini akan menghapus SEMUA data dan mengembalikan ke kondisi awal (seed). Lanjutkan?', false)) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        $this->info('Resetting database to factory state...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'form_approvals',
            'form_attachments',
            'form_pengembalian_items',
            'form_pengembalian',
            'form_pemeriksaan_items',
            'form_pemeriksaan',
            'form_perawatan_items',
            'form_perawatan',
            'activity_logs',
            'assets',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'employees',
            'users',
            'checklist_template_items',
            'checklist_templates',
            'positions',
            'sub_departements',
            'departements',
            'divisis',
            'directorates',
            'sites',
            'notifications',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->line("  Truncated: {$table}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Running all seeders...');

        $seeders = [
            'RoleAndPermissionSeeder',
            'ChecklistTemplateSeeder',
            'SiteSeeder',
            'OrganizationStructureSeeder',
            'UserSeeder',
            'AssetSeeder',
            'FormPemeriksaanSeeder',
            'FormPerawatanSeeder',
            'FormPengembalianSeeder',
            'FormApprovalSeeder',
        ];

        foreach ($seeders as $seeder) {
            $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        $this->newLine();
        $this->info('Reset selesai!');

        $this->newLine();
        $this->info('Record counts:');
        $this->line('  - users: ' . DB::table('users')->count());
        $this->line('  - employees: ' . DB::table('employees')->count());
        $this->line('  - sites: ' . DB::table('sites')->count());
        $this->line('  - assets: ' . DB::table('assets')->count());
        $this->line('  - checklist_templates: ' . DB::table('checklist_templates')->count());
        $this->line('  - form_pemeriksaan: ' . DB::table('form_pemeriksaan')->count());
        $this->line('  - form_perawatan: ' . DB::table('form_perawatan')->count());
        $this->line('  - form_pengembalian: ' . DB::table('form_pengembalian')->count());
        $this->line('  - form_approvals: ' . DB::table('form_approvals')->count());

        return 0;
    }
}
