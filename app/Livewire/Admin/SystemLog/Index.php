<?php

namespace App\Livewire\Admin\SystemLog;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    public array $logEntries = [];
    public string $search = '';
    public string $filterLevel = '';
    public int $maxLines = 500;
    public string $logContent = '';
    public bool $loading = true;

    public function mount(): void
    {
        $this->loadLogs();
    }

    public function loadLogs(): void
    {
        $this->loading = true;
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            $this->logEntries = [];
            $this->loading = false;
            return;
        }

        $content = file_get_contents($logFile);
        $this->logContent = $content;

        $lines = explode("\n", $content);
        $lines = array_filter($lines);
        $lines = array_reverse(array_slice(array_reverse($lines), 0, $this->maxLines));

        $entries = [];
        foreach ($lines as $line) {
            $parsed = $this->parseLine($line);
            if ($parsed) {
                $entries[] = $parsed;
            }
        }

        $this->logEntries = $entries;
        $this->loading = false;
    }

    private function parseLine(string $line): ?array
    {
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+):(.*)$/', $line, $matches)) {
            return [
                'datetime' => $matches[1],
                'environment' => $matches[2],
                'level' => strtolower($matches[3]),
                'message' => trim($matches[4]),
            ];
        }
        return null;
    }

    public function download(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $logFile = storage_path('logs/laravel.log');
        return response()->download($logFile, 'laravel-' . now()->format('Y-m-d_His') . '.log');
    }

    public function clear(): void
    {
        $logFile = storage_path('logs/laravel.log');
        file_put_contents($logFile, '');
        $this->logEntries = [];
        $this->logContent = '';
        session()->flash('success', 'System log berhasil dibersihkan.');
    }

    public function updatedSearch(): void
    {
        $this->loadLogs();
    }

    public function updatedFilterLevel(): void
    {
        $this->loadLogs();
    }

    public function updatedMaxLines(): void
    {
        $this->loadLogs();
    }

    public function render()
    {
        $entries = $this->logEntries;

        if ($this->search) {
            $entries = array_filter($entries, fn ($e) => str_contains(strtolower($e['message']), strtolower($this->search)));
        }

        if ($this->filterLevel) {
            $entries = array_filter($entries, fn ($e) => $e['level'] === $this->filterLevel);
        }

        $levels = array_unique(array_column($this->logEntries, 'level'));
        sort($levels);

        return view('livewire.admin.system-log.index', [
            'entries' => $entries,
            'levels' => $levels,
            'fileSize' => $this->logContent ? strlen($this->logContent) : 0,
        ]);
    }
}
