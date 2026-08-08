<?php

namespace App\Livewire\Admin\Sites;

use App\Helpers\ActivityLogger;
use App\Models\Site;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportCsv extends Component
{
    use WithFileUploads;

    public $file;
    public array $preview = [];
    public int $totalRows = 0;
    public int $successCount = 0;
    public int $errorCount = 0;
    public array $importErrors = [];
    public array $importSuccess = [];
    public string $resultTab = 'gagal';
    public array $validRows = [];
    public bool $processed = false;
    public bool $imported = false;
    public array $importedSites = [];
    public bool $showCancelModal = false;
    public bool $showConfirmModal = false;

    protected $listeners = ['resetImport' => 'resetImport'];

    public function resetImport(): void
    {
        $this->file = null;
        $this->preview = [];
        $this->totalRows = 0;
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->importErrors = [];
        $this->importSuccess = [];
        $this->resultTab = 'gagal';
        $this->validRows = [];
        $this->processed = false;
        $this->imported = false;
        $this->importedSites = [];
        $this->showCancelModal = false;
        $this->showConfirmModal = false;
        $this->resetValidation();
    }

    public function updatedFile(): void
    {
        $this->preview = [];
        $this->totalRows = 0;
        $this->importErrors = [];
        $this->importSuccess = [];
        $this->validRows = [];
        $this->processed = false;
        $this->imported = false;

        if (!$this->file) return;

        try {
            $this->validate(
                ['file' => 'required|mimes:csv,txt|max:10240'],
                [
                    'file.required' => 'Pilih file CSV terlebih dahulu',
                    'file.mimes' => 'File harus berformat .csv atau .txt',
                    'file.max' => 'Ukuran file melebihi batas maksimal (10MB)',
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('file', $e->validator->errors()->first('file'));
            $this->file = null;
            $this->dispatch('show-toast', message: 'Upload CSV gagal: ' . $e->validator->errors()->first('file'), type: 'error');
            return;
        }

        $this->loadPreview();

        if (!empty($this->importErrors)) {
            $this->dispatch('show-toast', message: 'Data CSV tidak sesuai: ' . $this->importErrors[0], type: 'error');
        }
    }

    private function loadPreview(): void
    {
        $handle = fopen($this->file->getPathname(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            $this->importErrors[] = 'File CSV kosong atau format tidak valid.';
            return;
        }

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        $normalizedHeader = array_map('strtolower', array_map('trim', $header));
        $requiredColumns = ['id_site', 'site'];
        $missingColumns = array_diff($requiredColumns, $normalizedHeader);

        if (!empty($missingColumns)) {
            $this->importErrors[] = 'Kolom wajib tidak ditemukan: ' . implode(', ', $missingColumns);
            return;
        }

        $rows = [];
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) continue;

            $data = array_combine($normalizedHeader, array_slice($row, 0, count($normalizedHeader)));
            $rows[] = $data;
            $count++;

            if ($count >= 5) break;
        }
        fclose($handle);

        $handle = fopen($this->file->getPathname(), 'r');
        fgetcsv($handle);
        $this->totalRows = 0;
        while (fgetcsv($handle) !== false) {
            $this->totalRows++;
        }
        fclose($handle);

        $this->preview = $rows;
    }

    public function processData(): void
    {
        if (!$this->file) return;

        set_time_limit(0);

        $this->successCount = 0;
        $this->errorCount = 0;
        $this->importErrors = [];
        $this->importSuccess = [];
        $this->validRows = [];
        $this->resultTab = 'gagal';

        $handle = fopen($this->file->getPathname(), 'r');
        $header = fgetcsv($handle);

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        $normalizedHeader = array_map('strtolower', array_map('trim', $header));

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count($row) < count($header)) continue;

            if (count($row) > count($normalizedHeader)) {
                $this->importErrors[] = "Baris {$rowNumber}: jumlah kolom (" . count($row) . ") tidak sesuai header (" . count($header) . "), baris dilewati.";
                $this->errorCount++;
                continue;
            }

            $data = array_combine($normalizedHeader, $row);

            try {
                $idSite = trim($data['id_site'] ?? '');
                $siteName = trim($data['site'] ?? '');

                if (empty($idSite) || empty($siteName)) {
                    $this->importErrors[] = "Baris {$rowNumber}: id_site dan site wajib diisi.";
                    $this->errorCount++;
                    continue;
                }

                $this->validRows[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'id_site' => $idSite,
                        'site' => $siteName,
                        'buss' => trim($data['buss'] ?? '') ?: null,
                        'id_corp' => trim($data['id_corp'] ?? '') ?: null,
                        'country' => trim($data['country'] ?? '') ?: null,
                        'provincy' => trim($data['provincy'] ?? '') ?: null,
                        'city' => trim($data['city'] ?? '') ?: null,
                        'address' => trim($data['address'] ?? '') ?: null,
                        'url_maps' => trim($data['url_maps'] ?? '') ?: null,
                    ],
                ];

                $this->importSuccess[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'id_site' => $idSite,
                        'site' => $siteName,
                        'buss' => trim($data['buss'] ?? '') ?: '-',
                        'id_corp' => trim($data['id_corp'] ?? '') ?: '-',
                        'country' => trim($data['country'] ?? '') ?: '-',
                        'provincy' => trim($data['provincy'] ?? '') ?: '-',
                        'city' => trim($data['city'] ?? '') ?: '-',
                        'address' => trim($data['address'] ?? '') ?: '-',
                        'url_maps' => trim($data['url_maps'] ?? '') ?: '-',
                    ],
                ];

                $this->successCount++;
            } catch (\Exception $e) {
                $this->importErrors[] = "Baris {$rowNumber}: " . $e->getMessage();
                $this->errorCount++;
            }
        }
        fclose($handle);

        $this->processed = true;

        if ($this->errorCount > 0) {
            $this->dispatch('show-toast', message: "Data terbaca: {$this->successCount} berhasil, {$this->errorCount} gagal. Periksa detail sebelum mengirim.", type: 'error');
        } else {
            $this->dispatch('show-toast', message: "Data terbaca: {$this->successCount} data valid. Klik 'Konfirmasi Kirim Data' untuk menyimpan.", type: 'success');
        }
    }

    public function confirmImport()
    {
        if (!$this->processed) {
            $this->dismissConfirmImport();
            $this->dispatch('show-toast', message: 'Tidak ada data untuk dikirim. Proses file CSV terlebih dahulu.', type: 'error');
            return;
        }

        if ($this->imported) {
            // The confirmation modal was restored from a stale snapshot (e.g. browser
            // back after a successful import). Data is already committed, so just take
            // the user to the list instead of silently doing nothing.
            $this->dismissConfirmImport();

            return redirect()->route('admin.sites.index');
        }

        set_time_limit(0);

        $importedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->validRows as $validRow) {
                $data = $validRow['data'];

                $attributes = [
                    'site' => $data['site'],
                    'buss' => $data['buss'],
                    'id_corp' => $data['id_corp'],
                    'country' => $data['country'],
                    'provincy' => $data['provincy'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'url_maps' => $data['url_maps'],
                ];

                $existing = Site::find($data['id_site']);

                if ($existing) {
                    $original = [
                        'site' => $existing->site,
                        'buss' => $existing->buss,
                        'id_corp' => $existing->id_corp,
                        'country' => $existing->country,
                        'provincy' => $existing->provincy,
                        'city' => $existing->city,
                        'address' => $existing->address,
                        'url_maps' => $existing->url_maps,
                    ];
                    $existing->update($attributes);
                    $this->importedSites[] = ['id_site' => $data['id_site'], 'existed' => true, 'original' => $original];
                } else {
                    Site::create(array_merge(['id_site' => $data['id_site']], $attributes));
                    $this->importedSites[] = ['id_site' => $data['id_site'], 'existed' => false, 'original' => null];
                }

                $importedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            $this->dispatch('show-toast', message: 'Kirim data gagal (perubahan dibatalkan): ' . $e->getMessage(), type: 'error');

            return;
        }

        $this->imported = true;
        $this->showConfirmModal = false;

        $message = $this->errorCount > 0
            ? "Import selesai: {$importedCount} berhasil, {$this->errorCount} gagal."
            : "Import selesai: {$importedCount} data berhasil diimpor.";

        ActivityLogger::log('import', "Mengimpor {$importedCount} data site" . ($this->errorCount ? " ({$this->errorCount} gagal)" : ''));

        session()->flash('success', $message);

        return redirect()->route('admin.sites.index');
    }

    public function confirmSendImport(): void
    {
        $this->showConfirmModal = true;
    }

    public function dismissConfirmImport(): void
    {
        $this->showConfirmModal = false;
    }

    public function confirmCancelImport(): void
    {
        $this->showCancelModal = true;
    }

    public function dismissCancelImport(): void
    {
        $this->showCancelModal = false;
    }

    public function cancelImport(): void
    {
        if (!$this->imported) {
            if ($this->processed) {
                $this->dispatch('show-toast', message: 'Import dibatalkan. Data belum dikirim ke database.', type: 'success');
                $this->resetImport();
            }
            return;
        }

        $count = count($this->importedSites);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->importedSites as $importedSite) {
                $site = Site::find($importedSite['id_site']);
                if (!$site) continue;

                if ($importedSite['existed']) {
                    $site->update($importedSite['original']);
                } else {
                    $site->delete();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            $this->dismissCancelImport();
            $this->dispatch('show-toast', message: 'Batalkan import gagal (perubahan dibatalkan): ' . $e->getMessage(), type: 'error');

            return;
        }

        ActivityLogger::log('delete', "Membatalkan import: mengembalikan/hapus {$count} data site");
        $this->dispatch('show-toast', message: "Import dibatalkan: {$count} data site dikembalikan/dihapus.", type: 'success');
        $this->resetImport();
    }

    public function render()
    {
        return view('livewire.admin.sites.import-csv');
    }
}
