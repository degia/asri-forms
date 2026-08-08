<?php

namespace App\Livewire\Admin\Employees;

use App\Helpers\ActivityLogger;
use App\Models\Departement;
use App\Models\Directorate;
use App\Models\Divisi;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Site;
use App\Models\SubDepartement;
use App\Models\User;
use Illuminate\Support\Carbon;
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
    public array $importedEmployees = [];
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
        $this->importedEmployees = [];
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
        } else {
            $this->dispatch('show-toast', message: "File berhasil diunggah: {$this->totalRows} baris terdeteksi. Klik 'Proses Load' untuk memvalidasi.", type: 'success');
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
        $requiredColumns = ['name'];
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

    private function statusList(): array
    {
        return [Employee::STATUS_ACTIVE, Employee::STATUS_RESIGNED];
    }

    private function resolveStatus(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'active', 'enable' => Employee::STATUS_ACTIVE,
            'resigned', 'disable' => Employee::STATUS_RESIGNED,
            default => null,
        };
    }

    private function resolveOrgName(string $value, string $model): ?int
    {
        $name = trim($value);

        if ($name === '') {
            return null;
        }

        return $model::where('name', $name)->value('id');
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

        // Pre-fetch lookups once instead of querying the database per row.
        // withTrashed() is required: a soft-deleted user still occupies the unique
        // email index, so their email must be treated as already registered.
        $userEmails = User::withTrashed()->pluck('email')
            ->map(fn ($email) => strtolower(trim($email)))
            ->flip()
            ->all();

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
                $name = trim($data['name'] ?? '');
                if (empty($name)) {
                    $this->importErrors[] = "Baris {$rowNumber}: Nama wajib diisi.";
                    $this->errorCount++;
                    continue;
                }

                $nik = trim($data['nik'] ?? '') ?: null;
                if ($nik !== null && Employee::withTrashed()->where('nik', $nik)->exists()) {
                    $this->importErrors[] = "Baris {$rowNumber}: NIK sudah terdaftar ({$nik}).";
                    $this->errorCount++;
                    continue;
                }

                $site = trim($data['site'] ?? '') ?: null;
                if ($site !== null && ! Site::where('id_site', $site)->exists()) {
                    $this->importErrors[] = "Baris {$rowNumber}: Site tidak ditemukan ({$site}).";
                    $this->errorCount++;
                    continue;
                }

                $directorateId = $this->resolveOrgName($data['directorate'] ?? '', Directorate::class);
                $divisiId = $this->resolveOrgName($data['divisi'] ?? '', Divisi::class);
                $departementId = $this->resolveOrgName($data['departement'] ?? '', Departement::class);
                $subDepartementId = $this->resolveOrgName($data['sub_departement'] ?? '', SubDepartement::class);
                $positionId = $this->resolveOrgName($data['position'] ?? '', Position::class);

                if (($data['directorate'] ?? '') !== '' && $directorateId === null) {
                    $this->importErrors[] = "Baris {$rowNumber}: Directorat tidak ditemukan (" . trim($data['directorate']) . ').';
                    $this->errorCount++;
                    continue;
                }
                if (($data['divisi'] ?? '') !== '' && $divisiId === null) {
                    $this->importErrors[] = "Baris {$rowNumber}: Divisi tidak ditemukan (" . trim($data['divisi']) . ').';
                    $this->errorCount++;
                    continue;
                }
                if (($data['departement'] ?? '') !== '' && $departementId === null) {
                    $this->importErrors[] = "Baris {$rowNumber}: Departemen tidak ditemukan (" . trim($data['departement']) . ').';
                    $this->errorCount++;
                    continue;
                }
                if (($data['sub_departement'] ?? '') !== '' && $subDepartementId === null) {
                    $this->importErrors[] = "Baris {$rowNumber}: Sub Departemen tidak ditemukan (" . trim($data['sub_departement']) . ').';
                    $this->errorCount++;
                    continue;
                }
                if (($data['position'] ?? '') !== '' && $positionId === null) {
                    $this->importErrors[] = "Baris {$rowNumber}: Posisi tidak ditemukan (" . trim($data['position']) . ').';
                    $this->errorCount++;
                    continue;
                }

                if ($divisiId !== null && $directorateId !== null
                    && ! Divisi::where('id', $divisiId)->where('directorate_id', $directorateId)->exists()) {
                    $this->importErrors[] = "Baris {$rowNumber}: Divisi tidak termasuk dalam Directorat terpilih.";
                    $this->errorCount++;
                    continue;
                }
                if ($departementId !== null && $divisiId !== null
                    && ! Departement::where('id', $departementId)->where('divisi_id', $divisiId)->exists()) {
                    $this->importErrors[] = "Baris {$rowNumber}: Departemen tidak termasuk dalam Divisi terpilih.";
                    $this->errorCount++;
                    continue;
                }
                if ($subDepartementId !== null && $departementId !== null
                    && ! SubDepartement::where('id', $subDepartementId)->where('departement_id', $departementId)->exists()) {
                    $this->importErrors[] = "Baris {$rowNumber}: Sub Departemen tidak termasuk dalam Departemen terpilih.";
                    $this->errorCount++;
                    continue;
                }

                $email = trim($data['email'] ?? '') ?: null;
                if ($email !== null) {
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->importErrors[] = "Baris {$rowNumber}: Format email tidak valid ({$email}).";
                        $this->errorCount++;
                        continue;
                    }

                    if (! isset($userEmails[strtolower(trim($email))])) {
                        $this->importErrors[] = "Baris {$rowNumber}: Email harus terdaftar sebagai akun user terlebih dahulu ({$email}).";
                        $this->errorCount++;
                        continue;
                    }

                    if (Employee::where('email', $email)->exists()) {
                        $this->importErrors[] = "Baris {$rowNumber}: Email sudah digunakan oleh employee lain ({$email}).";
                        $this->errorCount++;
                        continue;
                    }
                }

                $status = $this->resolveStatus($data['status'] ?? '') ?? Employee::STATUS_ACTIVE;

                $dateResign = null;
                $rawDateResign = trim($data['date_resign'] ?? '');
                if ($rawDateResign !== '') {
                    try {
                        $dateResign = Carbon::parse($rawDateResign)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $this->importErrors[] = "Baris {$rowNumber}: Format tanggal resign tidak valid ({$rawDateResign}). Gunakan format Y-m-d.";
                        $this->errorCount++;
                        continue;
                    }
                }

                $this->validRows[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'name' => $name,
                        'nik' => $nik,
                        'site' => $site,
                        'directorate_id' => $directorateId,
                        'divisi_id' => $divisiId,
                        'departement_id' => $departementId,
                        'sub_departement_id' => $subDepartementId,
                        'position_id' => $positionId,
                        'no_telepon' => trim($data['no_telepon'] ?? '') ?: null,
                        'email' => $email,
                        'status' => $status,
                        'date_resign' => $dateResign,
                    ],
                ];

                $this->importSuccess[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'name' => $name,
                        'nik' => $nik ?? '(auto)',
                        'email' => $email ?? '-',
                        'status' => $status,
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

    private function applyLinkage(Employee $employee, ?string $email, string $status): void
    {
        $previousUser = User::where('nik', $employee->nik)->first();

        if ($email === null) {
            if ($previousUser && $previousUser->email !== $employee->email) {
                $previousUser->update(['nik' => null]);
            }

            return;
        }

        $user = User::find($email);

        if (! $user) {
            return;
        }

        if ($previousUser && $previousUser->email !== $user->email) {
            $previousUser->update(['nik' => null]);
        }

        $user->update(['nik' => $employee->nik]);

        if ($status === Employee::STATUS_RESIGNED) {
            $user->update(['status' => User::STATUS_RESIGNED]);

            return;
        }

        $user->update(['status' => User::STATUS_ACTIVE]);
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

            return redirect()->route('admin.employees.index');
        }

        set_time_limit(0);

        $importedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->validRows as $validRow) {
                $data = $validRow['data'];
                $status = $data['status'];

                $attributes = [
                    'name' => $data['name'],
                    'nik' => $data['nik'],
                    'site' => $data['site'],
                    'directorate_id' => $data['directorate_id'],
                    'divisi_id' => $data['divisi_id'],
                    'departement_id' => $data['departement_id'],
                    'sub_departement_id' => $data['sub_departement_id'],
                    'position_id' => $data['position_id'],
                    'no_telepon' => $data['no_telepon'],
                    'email' => $data['email'],
                    'status' => $status,
                    'date_resign' => $status === Employee::STATUS_RESIGNED
                        ? ($data['date_resign'] ?? today()->format('Y-m-d'))
                        : null,
                    'akun_login' => $data['email'] ? 'Connect' : 'No Access',
                ];

                $existing = $data['nik'] !== null ? Employee::find($data['nik']) : null;

                if ($existing) {
                    $original = [
                        'name' => $existing->name,
                        'site' => $existing->site,
                        'directorate_id' => $existing->directorate_id,
                        'divisi_id' => $existing->divisi_id,
                        'departement_id' => $existing->departement_id,
                        'sub_departement_id' => $existing->sub_departement_id,
                        'position_id' => $existing->position_id,
                        'no_telepon' => $existing->no_telepon,
                        'email' => $existing->email,
                        'status' => $existing->status,
                        'date_resign' => $existing->date_resign,
                        'akun_login' => $existing->akun_login,
                        'user_nik' => $existing->user?->nik,
                        'user_status' => $existing->user?->status,
                    ];

                    $existing->update($attributes);
                    $employee = $existing;

                    $this->importedEmployees[] = ['nik' => $employee->nik, 'existed' => true, 'original' => $original];
                } else {
                    $employee = Employee::create($attributes);

                    $this->importedEmployees[] = ['nik' => $employee->nik, 'existed' => false, 'original' => null];
                }

                $this->applyLinkage($employee, $data['email'], $status);

                $importedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            $this->dismissConfirmImport();
            session()->flash('error', 'Kirim data gagal (perubahan dibatalkan): ' . $e->getMessage());

            return redirect()->route('admin.employees.index');
        }

        $this->imported = true;
        $this->showConfirmModal = false;

        if ($importedCount === 0) {
            session()->flash('error', 'Tidak ada data yang berhasil diimpor. Periksa kembali file CSV.');

            return redirect()->route('admin.employees.index');
        }

        $message = $this->errorCount > 0
            ? "Import selesai: {$importedCount} berhasil, {$this->errorCount} gagal."
            : "Import selesai: {$importedCount} data berhasil diimpor.";

        ActivityLogger::log('import', "Mengimpor {$importedCount} data employee" . ($this->errorCount ? " ({$this->errorCount} gagal)" : ''));

        session()->flash('success', $message);

        return redirect()->route('admin.employees.index');
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

        $count = count($this->importedEmployees);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->importedEmployees as $imported) {
                $employee = Employee::find($imported['nik']);
                if (!$employee) continue;

                if ($imported['existed']) {
                    $original = $imported['original'];

                    $employee->update([
                        'name' => $original['name'],
                        'site' => $original['site'],
                        'directorate_id' => $original['directorate_id'],
                        'divisi_id' => $original['divisi_id'],
                        'departement_id' => $original['departement_id'],
                        'sub_departement_id' => $original['sub_departement_id'],
                        'position_id' => $original['position_id'],
                        'no_telepon' => $original['no_telepon'],
                        'email' => $original['email'],
                        'status' => $original['status'],
                        'date_resign' => $original['date_resign'],
                        'akun_login' => $original['akun_login'],
                    ]);

                    $user = $employee->user;
                    if ($user) {
                        $user->update([
                            'nik' => $original['user_nik'],
                            'status' => $original['user_status'],
                        ]);
                    }
                } else {
                    $employee->delete();
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

        ActivityLogger::log('delete', "Membatalkan import: mengembalikan/menghapus {$count} data employee");
        $this->dispatch('show-toast', message: "Import dibatalkan: {$count} data employee dikembalikan/dihapus.", type: 'success');
        $this->resetImport();
    }

    public function render()
    {
        return view('livewire.admin.employees.import-csv');
    }
}
