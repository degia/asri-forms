<?php

namespace App\Livewire\Admin\Assets;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\Employee;
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
    public array $importedAssets = [];
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
        $this->importedAssets = [];
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
        $requiredColumns = ['no_asset', 'kategori', 'brand'];
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

            $data = array_combine($normalizedHeader, array_slice($row, 0, count($normalizedHeader)));

            try {
                $noAsset = trim($data['no_asset'] ?? '');
                $kategori = trim($data['kategori'] ?? '');
                $brand = trim($data['brand'] ?? '');

                if (empty($noAsset) || empty($kategori) || empty($brand)) {
                    $this->importErrors[] = "Baris {$rowNumber}: no_asset, kategori, dan brand wajib diisi.";
                    $this->errorCount++;
                    continue;
                }

                $assignedEmail = trim($data['assigned_employee_email'] ?? $data['assigned_user_email'] ?? '');

                $this->validRows[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'no_asset' => $noAsset,
                        'kategori' => $kategori,
                        'brand' => $brand,
                        'tipe' => trim($data['tipe'] ?? '') ?: '',
                        'nama_perangkat' => trim($data['nama_perangkat'] ?? '') ?: $noAsset,
                        'no_serial' => trim($data['no_serial'] ?? '') ?: null,
                        'operating_unit' => trim($data['operating_unit'] ?? '') ?: null,
                        'site_location_asset' => trim($data['site_location_asset'] ?? '') ?: null,
                        'assigned_employee_email' => $assignedEmail,
                    ],
                ];

                $this->importSuccess[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'no_asset' => $noAsset,
                        'kategori' => $kategori,
                        'brand' => $brand,
                        'tipe' => trim($data['tipe'] ?? '') ?: '-',
                        'nama_perangkat' => trim($data['nama_perangkat'] ?? '') ?: $noAsset,
                        'no_serial' => trim($data['no_serial'] ?? '') ?: '-',
                        'operating_unit' => trim($data['operating_unit'] ?? '') ?: '-',
                        'site_location_asset' => trim($data['site_location_asset'] ?? '') ?: '-',
                        'assigned_employee_email' => $assignedEmail ?: '-',
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

            return redirect()->route('admin.assets.index');
        }

        set_time_limit(0);

        $importedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->validRows as $validRow) {
                $data = $validRow['data'];

                $assignedEmployeeId = null;
                $assignedEmail = trim($data['assigned_employee_email'] ?? '');
                if ($assignedEmail !== '') {
                    $employee = Employee::where('email', $assignedEmail)
                        ->orWhere('nik', $assignedEmail)
                        ->first();
                    $assignedEmployeeId = $employee?->nik;
                }

                $attributes = [
                    'kategori' => $data['kategori'],
                    'brand' => $data['brand'],
                    'tipe' => $data['tipe'],
                    'nama_perangkat' => $data['nama_perangkat'],
                    'no_serial' => $data['no_serial'],
                    'qr_code' => $data['no_asset'],
                    'operating_unit' => $data['operating_unit'],
                    'site_location_asset' => $data['site_location_asset'],
                    'assigned_employee_id' => $assignedEmployeeId,
                    'status' => $assignedEmployeeId ? 'active' : 'inactive',
                ];

                $existing = Asset::where('no_asset', $data['no_asset'])->first();

                if ($existing) {
                    $original = $existing->only(['kategori', 'brand', 'tipe', 'nama_perangkat', 'no_serial', 'operating_unit', 'site_location_asset', 'assigned_employee_id', 'status']);
                    $existing->update($attributes);
                    $this->importedAssets[] = ['no_asset' => $data['no_asset'], 'existed' => true, 'original' => $original];
                } else {
                    Asset::create(array_merge(['no_asset' => $data['no_asset']], $attributes));
                    $this->importedAssets[] = ['no_asset' => $data['no_asset'], 'existed' => false, 'original' => null];
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

        ActivityLogger::log('import', "Mengimpor {$importedCount} data asset" . ($this->errorCount ? " ({$this->errorCount} gagal)" : ''));

        session()->flash('success', $message);

        return redirect()->route('admin.assets.index');
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

        $count = count($this->importedAssets);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->importedAssets as $importedAsset) {
                $asset = Asset::where('no_asset', $importedAsset['no_asset'])->first();
                if (!$asset) continue;

                if ($importedAsset['existed']) {
                    $asset->update($importedAsset['original']);
                } else {
                    $asset->forceDelete();
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

        ActivityLogger::log('delete', "Membatalkan import: mengembalikan/hapus {$count} data asset");
        $this->dispatch('show-toast', message: "Import dibatalkan: {$count} data asset dikembalikan/dihapus.", type: 'success');
        $this->resetImport();
    }

    public function render()
    {
        return view('livewire.admin.assets.import-csv');
    }
}
