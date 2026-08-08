<?php

namespace Tests\Feature;

use App\Livewire\Admin\Assets\ImportCsv;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class TmpImportFixTest extends TestCase
{
    public function test_process_load_shows_processed_panel(): void
    {
        $csv = "no_asset,kategori,brand,tipe\n" .
            "AST-001,Laptop,Dell,XPS 15\n" .
            "AST-002,Monitor,Samsung,U28E\n";

        $component = Livewire::test(ImportCsv::class)
            ->set('file', UploadedFile::fake()->createWithContent('assets.csv', $csv));

        $component->call('processData');

        $this->assertTrue($component->get('processed'));

        $html = $component->html();

        $this->assertStringContainsString(__('Data Terbaca'), $html);
        $this->assertStringContainsString(__('Konfirmasi Kirim Data'), $html);
    }

    public function test_process_load_panel_hidden_before_process(): void
    {
        $csv = "no_asset,kategori,brand,tipe\n" .
            "AST-001,Laptop,Dell,XPS 15\n";

        $component = Livewire::test(ImportCsv::class)
            ->set('file', UploadedFile::fake()->createWithContent('assets.csv', $csv));

        $html = $component->html();

        $this->assertStringNotContainsString(__('Data Terbaca'), $html);
    }
}
