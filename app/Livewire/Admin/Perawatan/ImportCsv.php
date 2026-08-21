<?php

namespace App\Livewire\Admin\Perawatan;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\FormPerawatan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportCsv extends Component
{
    use WithFileUploads;

    public $file;

    public array $preview = [];

    public int $totalForms = 0;

    public int $totalRows = 0;

    public int $successCount = 0;

    public int $errorCount = 0;

    public array $importErrors = [];

    public array $importSuccess = [];

    public string $resultTab = 'gagal';

    public array $validForms = [];

    public bool $processed = false;

    public bool $imported = false;

    public bool $showCancelModal = false;

    public bool $showConfirmModal = false;

    protected $listeners = ['resetImport' => 'resetImport'];

    public function resetImport(): void
    {
        $this->file = null;
        $this->preview = [];
        $this->totalForms = 0;
        $this->totalRows = 0;
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->importErrors = [];
        $this->importSuccess = [];
        $this->resultTab = 'gagal';
        $this->validForms = [];
        $this->processed = false;
        $this->imported = false;
        $this->showCancelModal = false;
        $this->showConfirmModal = false;
        $this->resetValidation();
    }

    public function updatedFile(): void
    {
        $this->preview = [];
        $this->totalForms = 0;
        $this->totalRows = 0;
        $this->importErrors = [];
        $this->importSuccess = [];
        $this->validForms = [];
        $this->processed = false;
        $this->imported = false;

        if (! $this->file) {
            return;
        }

        try {
            $this->validate(
                ['file' => 'required|mimes:csv,txt|max:10240'],
                [
                    'file.required' => 'Pilih file CSV terlebih dahulu',
                    'file.mimes' => 'File harus berformat .csv atau .txt',
                    'file.max' => 'Ukuran file melebihi batas maksimal (10MB)',
                ]
            );
        } catch (ValidationException $e) {
            $this->addError('file', $e->validator->errors()->first('file'));
            $this->file = null;
            $this->dispatch('show-toast', message: 'Upload CSV gagal: '.$e->validator->errors()->first('file'), type: 'error');

            return;
        }

        $this->loadPreview();

        if (! empty($this->importErrors)) {
            $this->dispatch('show-toast', message: 'Data CSV tidak sesuai: '.$this->importErrors[0], type: 'error');
        }
    }

    private function loadPreview(): void
    {
        $handle = fopen($this->file->getPathname(), 'r');
        $header = fgetcsv($handle);

        if (! $header) {
            $this->importErrors[] = 'File CSV kosong atau format tidak valid.';
            fclose($handle);

            return;
        }

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        $normalizedHeader = array_map('strtolower', array_map('trim', $header));
        $requiredColumns = ['nomor_form', 'pengguna_nik', 'asset_no_asset', 'site_location'];
        $missingColumns = array_diff($requiredColumns, $normalizedHeader);

        if (! empty($missingColumns)) {
            $this->importErrors[] = 'Kolom wajib tidak ditemukan: '.implode(', ', $missingColumns);
            fclose($handle);

            return;
        }

        $allRows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) {
                continue;
            }
            $data = array_combine($normalizedHeader, array_slice($row, 0, count($normalizedHeader)));
            $allRows[] = $data;
        }
        fclose($handle);

        $this->totalRows = count($allRows);
        $this->totalForms = 0;

        $grouped = [];
        foreach ($allRows as $row) {
            $nomor = trim($row['nomor_form'] ?? '');
            if ($nomor === '') {
                continue;
            }
            $grouped[$nomor][] = $row;
        }
        $this->totalForms = count($grouped);

        $previewForms = array_slice($grouped, 0, 3, true);
        $this->preview = [];
        foreach ($previewForms as $nomor => $formRows) {
            foreach ($formRows as $row) {
                $this->preview[] = $row;
            }
        }
    }

    public function processData(): void
    {
        if (! $this->file) {
            return;
        }

        set_time_limit(0);

        $this->successCount = 0;
        $this->errorCount = 0;
        $this->importErrors = [];
        $this->importSuccess = [];
        $this->validForms = [];
        $this->resultTab = 'gagal';

        $handle = fopen($this->file->getPathname(), 'r');
        $header = fgetcsv($handle);

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        $normalizedHeader = array_map('strtolower', array_map('trim', $header));

        $allRows = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count($row) < count($header)) {
                continue;
            }
            $data = array_combine($normalizedHeader, array_slice($row, 0, count($normalizedHeader)));
            $data['__row'] = $rowNumber;
            $allRows[] = $data;
        }
        fclose($handle);

        $grouped = [];
        foreach ($allRows as $row) {
            $nomor = trim($row['nomor_form'] ?? '');
            if ($nomor === '') {
                $this->importErrors[] = "Baris {$row['__row']}: nomor_form wajib diisi.";
                $this->errorCount++;

                continue;
            }
            $grouped[$nomor][] = $row;
        }

        $this->totalForms = count($grouped);

        foreach ($grouped as $nomor => $formRows) {
            $firstRow = $formRows[0];
            $firstRowNum = $firstRow['__row'];

            $penggunaNik = trim($firstRow['pengguna_nik'] ?? '');
            $assetNo = trim($firstRow['asset_no_asset'] ?? '');
            $siteLocation = trim($firstRow['site_location'] ?? '');

            if ($penggunaNik === '' || $assetNo === '' || $siteLocation === '') {
                $missing = [];
                if ($penggunaNik === '') {
                    $missing[] = 'pengguna_nik';
                }
                if ($assetNo === '') {
                    $missing[] = 'asset_no_asset';
                }
                if ($siteLocation === '') {
                    $missing[] = 'site_location';
                }
                $this->importErrors[] = "Form {$nomor} (baris {$firstRowNum}): kolom wajib kosong: ".implode(', ', $missing).'.';
                $this->errorCount++;

                continue;
            }

            $asset = Asset::where('no_asset', $assetNo)->first();
            if (! $asset) {
                $this->importErrors[] = "Form {$nomor} (baris {$firstRowNum}): asset '{$assetNo}' tidak ditemukan.";
                $this->errorCount++;

                continue;
            }

            $pengguna = Employee::where('nik', $penggunaNik)->first();
            if (! $pengguna) {
                $this->importErrors[] = "Form {$nomor} (baris {$firstRowNum}): employee dengan NIK '{$penggunaNik}' tidak ditemukan.";
                $this->errorCount++;

                continue;
            }

            $submittedAt = null;
            $submittedAtRaw = trim($firstRow['submitted_at'] ?? '');
            if ($submittedAtRaw !== '') {
                $submittedAt = Carbon::parse($submittedAtRaw);
                if (! $submittedAt->isValid()) {
                    $this->importErrors[] = "Form {$nomor} (baris {$firstRowNum}): format tanggal tidak valid '{$submittedAtRaw}'. Gunakan format Y/m/d H:i.";
                    $this->errorCount++;

                    continue;
                }
            }

            $items = [];
            $itemErrors = false;
            foreach ($formRows as $fr) {
                $itemCategory = trim($fr['item_category'] ?? '');
                $itemName = trim($fr['item_name'] ?? '');

                if ($itemCategory === '' || $itemName === '') {
                    $this->importErrors[] = "Form {$nomor} (baris {$fr['__row']}): item_category dan item_name wajib diisi.";
                    $this->errorCount++;
                    $itemErrors = true;
                    break;
                }

                $items[] = [
                    'category' => $itemCategory,
                    'name' => $itemName,
                    'status' => trim($fr['item_status'] ?? '') ?: null,
                    'keterangan' => trim($fr['item_keterangan'] ?? '') ?: null,
                    'full_charge_capacity' => trim($fr['item_full_charge_capacity'] ?? '') ?: null,
                    'design_capacity' => trim($fr['item_design_capacity'] ?? '') ?: null,
                    'sort_order' => (int) (trim($fr['item_sort_order'] ?? '0') ?: 0),
                ];
            }

            if ($itemErrors) {
                continue;
            }

            if (empty($items)) {
                $this->importErrors[] = "Form {$nomor}: minimal harus memiliki 1 item.";
                $this->errorCount++;

                continue;
            }

            $status = strtolower(trim($firstRow['status'] ?? '')) ?: 'draft';
            if (! in_array($status, ['draft', 'submitted', 'approved'])) {
                $status = 'draft';
            }

            $this->validForms[] = [
                'nomor_form' => $nomor,
                'first_row' => $firstRowNum,
                'data' => [
                    'nomor_form' => $nomor,
                    'submitted_at' => $submittedAt,
                    'pengguna_employee_id' => $penggunaNik,
                    'asset_id' => $asset->id,
                    'site_location' => $siteLocation,
                    'location_detail' => trim($firstRow['location_detail'] ?? '') ?: null,
                    'kondisi_akhir' => strtolower(trim($firstRow['kondisi_akhir'] ?? '')) ?: null,
                    'kondisi_akhir_notes' => trim($firstRow['kondisi_akhir_notes'] ?? '') ?: null,
                    'barcode_fisik' => trim($firstRow['barcode_fisik'] ?? '') ?: null,
                    'notes' => trim($firstRow['notes'] ?? '') ?: null,
                    'status' => $status,
                ],
                'items' => $items,
            ];

            $this->successCount++;

            $this->importSuccess[] = [
                'nomor_form' => $nomor,
                'pengguna' => $pengguna->name ?? $penggunaNik,
                'asset_no' => $assetNo,
                'items_count' => count($items),
                'status' => $status,
            ];
        }

        $this->processed = true;

        if ($this->errorCount > 0) {
            $this->dispatch('show-toast', message: "Data terbaca: {$this->successCount} form valid, {$this->errorCount} form gagal. Periksa detail sebelum mengirim.", type: 'error');
        } else {
            $this->dispatch('show-toast', message: "Data terbaca: {$this->successCount} form valid. Klik 'Konfirmasi Kirim Data' untuk menyimpan.", type: 'success');
        }
    }

    public function confirmImport(): void
    {
        if (! $this->processed) {
            $this->dismissConfirmImport();
            $this->dispatch('show-toast', message: 'Tidak ada data untuk dikirim. Proses file CSV terlebih dahulu.', type: 'error');

            return;
        }

        if ($this->imported) {
            $this->dismissConfirmImport();

            return redirect()->route('admin.perawatan.index');
        }

        set_time_limit(0);

        $importedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($this->validForms as $validForm) {
                $form = FormPerawatan::create($validForm['data']);

                foreach ($validForm['items'] as $itemData) {
                    $form->items()->create($itemData);
                }

                $importedCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->dispatch('show-toast', message: 'Kirim data gagal (perubahan dibatalkan): '.$e->getMessage(), type: 'error');

            return;
        }

        $this->imported = true;
        $this->showConfirmModal = false;

        $message = "Import selesai: {$importedCount} form perawatan berhasil diimpor.";

        ActivityLogger::log('import', "Mengimpor {$importedCount} form perawatan");

        session()->flash('success', $message);

        return redirect()->route('admin.perawatan.index');
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
        $this->dismissCancelImport();
        $this->dispatch('show-toast', message: 'Import dibatalkan. Data belum dikirim ke database.', type: 'success');
        $this->resetImport();
    }

    public function render()
    {
        return view('livewire.admin.perawatan.import-csv');
    }
}
