<?php

namespace App\Livewire\Admin\Backup;

use App\Helpers\ActivityLogger;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class Index extends Component
{
    use WithFileUploads;

    public bool $isCreating = false;
    public bool $isRestoring = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;
    public $uploadedFile = null;

    public function createBackup(): void
    {
        @set_time_limit(300);
        $this->isCreating = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $mysqldump = $this->findMysqldump();
            if (!$mysqldump) {
                throw new \Exception('mysqldump tidak ditemukan di sistem. Pastikan MySQL terinstall.');
            }

            $dir = storage_path('app/backups');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $sqlFile = "{$dir}/backup_{$timestamp}.sql";
            $zipPath = "{$dir}/backup_{$timestamp}.zip";

            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            $passwordArg = $password ? ' --password=' . escapeshellarg($password) : '';
            $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
            $errFile = $sqlFile . '.err';
            $cmd = sprintf(
                '%s --host=%s --port=%s --user=%s%s %s --routines --single-transaction --quick > %s 2>%s',
                escapeshellarg($mysqldump),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $passwordArg,
                escapeshellarg($database),
                escapeshellarg($sqlFile),
                escapeshellarg($errFile)
            );

            exec($cmd, $output, $exitCode);

            if (!file_exists($sqlFile) || filesize($sqlFile) === 0) {
                $error = file_exists($errFile) ? trim((string) file_get_contents($errFile)) : implode("\n", $output);
                @unlink($sqlFile);
                @unlink($errFile);
                throw new \Exception("Gagal membuat database dump" . ($error ? ": {$error}" : ''));
            }

            @unlink($errFile);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Gagal membuat file arsip zip');
            }

            $zip->addFile($sqlFile, 'database/' . basename($sqlFile));

            $storagePath = storage_path('app/public');
            if (is_dir($storagePath)) {
                $this->addToZip($zip, $storagePath, 'storage');
            }

            $zip->close();

            unlink($sqlFile);

            ActivityLogger::log('backup', "Membuat backup database: backup_{$timestamp}.zip", null, null, ['filename' => "backup_{$timestamp}.zip"]);
            $this->successMessage = "Backup berhasil dibuat: backup_{$timestamp}.zip";
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        $this->isCreating = false;
    }

    public function restoreBackup(string $filename): void
    {
        @set_time_limit(300);
        $this->isRestoring = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $mysql = $this->findMysql();
            if (!$mysql) {
                throw new \Exception('mysql client tidak ditemukan di sistem.');
            }

            $zipPath = storage_path('app/backups/' . basename($filename));
            if (!file_exists($zipPath)) {
                throw new \Exception('File backup tidak ditemukan.');
            }

            $tempDir = storage_path('app/backups/_restore_' . now()->format('Ymd_His'));
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Gagal membuka file arsip.');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            $sqlFiles = glob($tempDir . '/database/*.sql');
            if (empty($sqlFiles)) {
                $this->rrmdir($tempDir);
                throw new \Exception('Tidak ditemukan file database (.sql) dalam arsip backup.');
            }

            $sqlFile = $sqlFiles[0];
            $content = file_get_contents($sqlFile);
            $clean = implode("\n", array_filter(explode("\n", $content), fn($line) => !str_starts_with(trim($line), 'mysqldump:')));
            if ($clean !== $content) {
                file_put_contents($sqlFile, $clean);
            }

            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            $passwordArg = $password ? ' --password=' . escapeshellarg($password) : '';
            $cmd = sprintf(
                '%s --host=%s --port=%s --user=%s%s %s < %s 2>&1',
                escapeshellarg($mysql),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $passwordArg,
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            exec($cmd, $output, $exitCode);

            $this->rrmdir($tempDir);

            if ($exitCode !== 0) {
                $error = implode("\n", $output);
                throw new \Exception("Gagal merestore database" . ($error ? ": {$error}" : ''));
            }

            ActivityLogger::log('restore', "Merestore database dari backup: {$filename}", null, null, ['filename' => $filename]);
            $this->successMessage = "Database berhasil direstore dari {$filename}";
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        $this->isRestoring = false;
    }

    public function uploadAndRestore(): void
    {
        @set_time_limit(300);
        $this->errorMessage = null;
        $this->successMessage = null;

        if (!$this->uploadedFile) {
            $this->errorMessage = 'File belum terunggah. Pastikan file .sql/.zip sudah dipilih dan ukurannya tidak melebihi 50MB.';
            return;
        }

        $this->validate([
            'uploadedFile' => 'required|file|mimes:sql,zip|max:51200',
        ], [
            'uploadedFile.required' => 'File belum terunggah.',
            'uploadedFile.mimes' => 'Format file harus .sql atau .zip.',
            'uploadedFile.max' => 'Ukuran file maksimal 50MB.',
        ]);

        $this->isRestoring = true;

        try {
            $mysql = $this->findMysql();
            if (!$mysql) {
                throw new \Exception('mysql client tidak ditemukan di sistem.');
            }

            $suffix = now()->format('Ymd_His');
            $tempDir = storage_path("app/backups/_upload_{$suffix}");
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $originalName = $this->uploadedFile->getClientOriginalName();
            $extension = strtolower($this->uploadedFile->getClientOriginalExtension());
            $tempPath = $tempDir . '/upload.' . $extension;
            copy($this->uploadedFile->getRealPath(), $tempPath);

            $sqlFile = null;

            if ($extension === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($tempPath) !== true) {
                    throw new \Exception('Gagal membuka file arsip zip.');
                }
                $zip->extractTo($tempDir);
                $zip->close();

                $found = glob($tempDir . '/**/*.sql');
                if (empty($found)) {
                    $found = glob($tempDir . '/*.sql');
                }
                if (empty($found)) {
                    throw new \Exception('Tidak ditemukan file .sql dalam arsip zip yang diupload.');
                }
                $sqlFile = $found[0];
            } else {
                $sqlFile = $tempPath;
            }

            $content = file_get_contents($sqlFile);
            $clean = implode("\n", array_filter(explode("\n", $content), fn($line) => !str_starts_with(trim($line), 'mysqldump:')));
            if ($clean !== $content) {
                file_put_contents($sqlFile, $clean);
            }

            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            $passwordArg = $password ? ' --password=' . escapeshellarg($password) : '';
            $cmd = sprintf(
                '%s --host=%s --port=%s --user=%s%s %s < %s 2>&1',
                escapeshellarg($mysql),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $passwordArg,
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            exec($cmd, $output, $exitCode);

            $this->rrmdir($tempDir);

            if ($exitCode !== 0) {
                $error = implode("\n", $output);
                throw new \Exception("Gagal merestore database dari file yang diupload" . ($error ? ": {$error}" : ''));
            }

            $this->uploadedFile = null;
            ActivityLogger::log('restore', "Merestore database dari file upload: {$originalName}", null, null, ['filename' => $originalName]);
            $this->successMessage = "Database berhasil direstore dari {$originalName}";
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->rrmdir($tempDir);
            }
        }

        $this->isRestoring = false;
    }

    public function deleteBackup(string $filename): void
    {
        $path = storage_path('app/backups/' . basename($filename));
        if (file_exists($path)) {
            unlink($path);
            ActivityLogger::log('delete', "Menghapus file backup: {$filename}");
        }
    }

    public function getBackupsProperty(): array
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.zip');
        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'size_formatted' => $this->formatSize(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    private function findMysqldump(): ?string
    {
        return $this->findBinary('mysqldump');
    }

    private function findMysql(): ?string
    {
        return $this->findBinary('mysql');
    }

    private function findBinary(string $binary): ?string
    {
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        $cmd = $isWindows ? $binary . '.exe' : $binary;

        $envPath = getenv(strtoupper($binary) . '_BIN_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        $output = null;
        $code = null;
        if ($isWindows) {
            exec('where ' . $cmd . ' 2>NUL', $output, $code);
        } else {
            exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null', $output, $code);
        }
        if ($code === 0 && !empty($output[0])) {
            return $output[0];
        }

        $dirs = $isWindows ? [
            'C:\\laragon\\bin\\mysql',
            'C:\\laragon\\bin\\mariadb',
            'C:\\wamp64\\bin\\mysql',
            'C:\\xampp\\mysql\\bin',
            'C:\\Program Files\\MySQL',
            'C:\\Program Files\\MariaDB',
            'C:\\tools\\mysql',
        ] : [
            '/Applications/XAMPP/bin',
            '/Applications/MAMP/Library/bin',
            '/usr/local/mysql/bin',
            '/opt/homebrew/bin',
            '/usr/bin',
        ];

        foreach ($dirs as $dir) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . $cmd, GLOB_NOSORT) ?: [] as $file) {
                return $file;
            }
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $cmd, GLOB_NOSORT) ?: [] as $file) {
                return $file;
            }
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $cmd, GLOB_NOSORT) ?: [] as $file) {
                return $file;
            }
        }

        return null;
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    private function addToZip(ZipArchive $zip, string $path, string $parent): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $baseLength = strlen($path) + 1;
        foreach ($files as $file) {
            if (!$file->isFile()) continue;
            $realPath = $file->getRealPath();
            $relativePath = $parent . '/' . substr($realPath, $baseLength);
            $zip->addFile($realPath, $relativePath);
        }
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function render()
    {
        return view('livewire.admin.backup.index');
    }
}
