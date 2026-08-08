<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $template = DB::table('checklist_templates')
            ->where('name', 'Perawatan Aplikasi')
            ->where('form_type', 'perawatan')
            ->first();

        if (!$template) return;

        DB::table('checklist_template_items')
            ->where('template_id', $template->id)
            ->delete();

        $items = [
            ['name' => 'Antivirus (Kaspersky)', 'sort_order' => 1],
            ['name' => 'Manage Device (Endpoint Central)', 'sort_order' => 2],
            ['name' => 'Office 365', 'sort_order' => 3],
            ['name' => 'Remote (Anydesk)', 'sort_order' => 4],
            ['name' => 'Browser (Edge/Chrome)', 'sort_order' => 5],
            ['name' => 'PDF (Adobe Acrobat/PDF SAM/PDF Gear)', 'sort_order' => 6],
            ['name' => 'Onedrive', 'sort_order' => 7],
            ['name' => 'Teams', 'sort_order' => 8],
            ['name' => 'File Extraction (7-Zip)', 'sort_order' => 9],
        ];

        foreach ($items as $item) {
            DB::table('checklist_template_items')->insert(array_merge($item, [
                'template_id' => $template->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        $template = DB::table('checklist_templates')
            ->where('name', 'Perawatan Aplikasi')
            ->where('form_type', 'perawatan')
            ->first();

        if (!$template) return;

        DB::table('checklist_template_items')
            ->where('template_id', $template->id)
            ->delete();

        $items = [
            ['name' => 'Application Standard IT Check', 'sort_order' => 1],
            ['name' => 'Antivirus Kaspersky (Lisensi & Proteksi Aktif)', 'sort_order' => 2],
            ['name' => 'Manage Engine Endpoint Central (Terkoneksi Server)', 'sort_order' => 3],
        ];

        foreach ($items as $item) {
            DB::table('checklist_template_items')->insert(array_merge($item, [
                'template_id' => $template->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
