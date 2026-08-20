<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DeployOptimize extends Command
{
    protected $signature = 'app:optimize-deploy
                    {--clear : Hapus semua cache sebelum rebuild}
                    {--no-views : Skip view caching}
                    {--no-events : Skip event caching}
                    {--no-config : Skip config caching}
                    {--no-queue : Skip queue restart}';

    protected $description = 'Jalankan semua optimasi caching untuk production deploy';

    public function handle(): int
    {
        $this->newLine();
        $this->info('🚀 Deploy Optimization');
        $this->line('─────────────────────────────');

        if ($this->option('clear')) {
            $this->purgeAllCache();
        }

        if (! $this->option('no-config')) {
            $this->cacheConfig();
        }

        if (! $this->option('no-views')) {
            $this->cacheViews();
        }

        if (! $this->option('no-events')) {
            $this->cacheEvents();
        }

        $this->clearOldCache();

        if (! $this->option('no-queue')) {
            $this->restartQueue();
        }

        $this->line('─────────────────────────────');
        $this->info('✅ Deploy optimization selesai.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function purgeAllCache(): void
    {
        $this->info('🗑️  Membersihkan semua cache...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('event:clear');
        Artisan::call('view:clear');
        $this->line('   Semua cache dibersihkan.');
    }

    private function cacheConfig(): void
    {
        $this->info('⚙️  Caching config...');
        Artisan::call('config:cache');
        $this->line('   ' . Artisan::output());
    }

    private function cacheViews(): void
    {
        $this->info('📄 Caching views...');
        Artisan::call('view:cache');
        $this->line('   ' . Artisan::output());
    }

    private function cacheEvents(): void
    {
        $this->info('📡 Caching events...');
        Artisan::call('event:cache');
        $this->line('   ' . Artisan::output());
    }

    private function clearOldCache(): void
    {
        $cachePath = storage_path('framework/cache/data');
        if (File::isDirectory($cachePath)) {
            $files = File::files($cachePath);
            $this->info('🧹 ' . count($files) . ' cache files aktif.');
        }
    }

    private function restartQueue(): void
    {
        $this->info('🔄 Restarting queue workers...');
        Artisan::call('queue:restart');
        $this->line('   Queue workers akan restart secara graceful.');
    }
}
