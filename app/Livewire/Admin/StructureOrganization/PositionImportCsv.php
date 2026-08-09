<?php

namespace App\Livewire\Admin\StructureOrganization;

use App\Helpers\ActivityLogger;
use App\Models\Position;
use Livewire\Component;
use Livewire\WithFileUploads;

class PositionImportCsv extends Component
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
    public array $importedPositions = [];
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
        $this->importedPositions = [];
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('file', $e->validator->errors()->first('file'));
            $this->file = null;
            $this->dispatch('show-toast', message: 'Upload CSV gagal: '.$e->validator->errors()->first('file'), type: 'error');

            return;
        }

        $this->loadPreview();

        if (! empty($this->importErrors)) {
            $this->dispatch('show-toast', message: 'Data CSV tidak sesuai: '.$this->importErrors[0], type: 'error');
        } else {
            $this->dispatch('show-toast', message: "File berhasil diunggah: {$this->totalRows} baris terdeteksi. Klik 'Proses Load' untuk memvalidasi.", type: 'success');
        }
    }

    private function loadPreview(): void
    {
        $handle = fopen($this->file->getPathname(), 'r');
        $header = fgetcsv($handle);

        if (! $header) {
            $this->importErrors[] = 'File CSV kosong atau format tidak valid.';

            return;
        }

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        $normalizedHeader = array_map('strtolower', array_map('trim', $header));
        $requiredColumns = ['name'];
        $missingColumns = array_diff($requiredColumns, $normalizedHeader);

        if (! empty($missingColumns)) {
            $this->importErrors[] = 'Kolom wajib tidak ditemukan: '.implode(', ', $missingColumns);

            return;
        }

        $rows = [];
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) {
                continue;
            }

            $data = array_combine($normalizedHeader, array_slice($row, 0, count($normalizedHeader)));
            $rows[] = $data;
            $count++;

            if ($count >= 5) {
                break;
            }
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
        if (! $this->file) {
            return;
        }

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

        $seenNames = [];
        $seenCodes = [];

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count($row) < count($header)) {
                continue;
            }

            if (count($row) > count($normalizedHeader)) {
                $this->importErrors[] = "Baris {$rowNumber}: jumlah kolom (".count($row).") tidak sesuai header (".count($header).'), baris dilewati.';
                $this->errorCount++;

                continue;
            }

            $data = array_combine($normalizedHeader, $row);

            try {
                $name = trim($data['name'] ?? '');
                if ($name === '') {
                    $this->importErrors[] = "Baris {$rowNumber}: Nama wajib diisi.";
                    $this->errorCount++;

                    continue;
                }

                if (isset($seenNames[$name])) {
                    $this->importErrors[] = "Baris {$rowNumber}: Nama duplikat di dalam file ({$name}).";
                    $this->errorCount++;

                    continue;
                }
                $seenNames[$name] = true;

                $code = trim($data['code'] ?? '') ?: null;
                if ($code !== null && mb_strlen($code) > 50) {
                    $this->importErrors[] = "Baris {$rowNumber}: Code terlalu panjang (maksimal 50 karakter).";
                    $this->errorCount++;

                    continue;
                }

                if ($code !== null) {
                    if (isset($seenCodes[$code])) {
                        $this->importErrors[] = "Baris {$rowNumber}: Code duplikat di dalam file ({$code}).";
                        $this->errorCount++;

                        continue;
                    }

                    if (Position::where('code', $code)->where('name', '!=', $name)->exists()) {
                        $this->importErrors[] = "Baris {$rowNumber}: Code sudah digunakan ({$code}).";
                        $this->errorCount++;

                        continue;
                    }

                    $seenCodes[$code] = true;
                }

                $sortOrder = 0;
                $rawSortOrder = trim($data['sort_order'] ?? '');
                if ($rawSortOrder !== '') {
                    if (! ctype_digit($rawSortOrder)) {
                        $this->importErrors[] = "Baris {$rowNumber}: Urutan harus berupa angka bulat (sort_order).";
                        $this->errorCount++;

                        continue;
                    }
                    $sortOrder = (int) $rawSortOrder;
                }

                $existing = Position::where('name', $name)->first();

                $this->validRows[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'name' => $name,
                        'code' => $code,
                        'sort_order' => $sortOrder,
                        'existing_id' => $existing?->id,
                    ],
                ];

                $this->importSuccess[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'name' => $name,
                        'code' => $code ?? '-',
                        'sort_order' => $sortOrder,
                    ],
                ];

                $this->successCount++;
            } catch (\Exception $e) {
                $this->importErrors[] = "Baris {$rowNumber}: ".$e->getMessage();
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
        if (! $this->processed) {
            $this->dismissConfirmImport();
            $this->dispatch('show-toast', message: 'Tidak ada data untuk dikirim. Proses file CSV terlebih dahulu.', type: 'error');

            return;
        }

        if ($this->imported) {
            $this->dismissConfirmImport();

            return redirect()->route('admin.structure-organization.index');
        }

        set_time_limit(0);

        $importedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->validRows as $validRow) {
                $data = $validRow['data'];

                $attributes = [
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'sort_order' => $data['sort_order'],
                ];

                if ($data['existing_id']) {
                    $position = Position::find($data['existing_id']);

                    if (! $position) {
                        $position = Position::create($attributes);
                        $this->importedPositions[] = ['id' => $position->id, 'existed' => false, 'original' => null];
                    } else {
                        $original = [
                            'name' => $position->name,
                            'code' => $position->code,
                            'sort_order' => $position->sort_order,
                        ];

                        $position->update($attributes);

                        $this->importedPositions[] = ['id' => $position->id, 'existed' => true, 'original' => $original];
                    }
                } else {
                    $position = Position::create($attributes);

                    $this->importedPositions[] = ['id' => $position->id, 'existed' => false, 'original' => null];
                }

                $importedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            $this->dismissConfirmImport();
            session()->flash('error', 'Kirim data gagal (perubahan dibatalkan): '.$e->getMessage());

            return redirect()->route('admin.structure-organization.index');
        }

        $this->imported = true;
        $this->showConfirmModal = false;

        if ($importedCount === 0) {
            session()->flash('error', 'Tidak ada data yang berhasil diimpor. Periksa kembali file CSV.');

            return redirect()->route('admin.structure-organization.index');
        }

        $message = $this->errorCount > 0
            ? "Import selesai: {$importedCount} berhasil, {$this->errorCount} gagal."
            : "Import selesai: {$importedCount} data position berhasil diimpor.";

        ActivityLogger::log('import', "Mengimpor {$importedCount} data position".($this->errorCount ? " ({$this->errorCount} gagal)" : ''));

        session()->flash('success', $message);

        return redirect()->route('admin.structure-organization.index');
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
        if (! $this->imported) {
            if ($this->processed) {
                $this->dispatch('show-toast', message: 'Import dibatalkan. Data belum dikirim ke database.', type: 'success');
                $this->resetImport();
            }

            return;
        }

        $count = count($this->importedPositions);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->importedPositions as $imported) {
                $position = Position::find($imported['id']);
                if (! $position) {
                    continue;
                }

                if ($imported['existed']) {
                    $original = $imported['original'];

                    $position->update([
                        'name' => $original['name'],
                        'code' => $original['code'],
                        'sort_order' => $original['sort_order'],
                    ]);
                } else {
                    $position->delete();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            $this->dismissCancelImport();
            $this->dispatch('show-toast', message: 'Batalkan import gagal (perubahan dibatalkan): '.$e->getMessage(), type: 'error');

            return;
        }

        ActivityLogger::log('delete', "Membatalkan import: mengembalikan/menghapus {$count} data position");
        $this->dispatch('show-toast', message: "Import dibatalkan: {$count} data position dikembalikan/dihapus.", type: 'success');
        $this->resetImport();
    }

    public function render()
    {
        return view('livewire.admin.structure-organization.position-import-csv');
    }
}
