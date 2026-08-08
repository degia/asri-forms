<?php

namespace App\Livewire\Users;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
    public array $importedIds = [];
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
        $this->importedIds = [];
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
                $this->dispatch('show-toast', message: "File berhasil diunggah: {$this->totalRows} baris terdeteksi. Klik 'Import' untuk memproses.", type: 'success');
            }
        } catch (\Throwable $e) {
            // Never let a parsing error fail silently (would leave the UI stuck at "Memproses file").
            $this->file = null;
            $this->importErrors[] = 'Gagal membaca file CSV: ' . $e->getMessage();
            $this->dispatch('show-toast', message: 'Upload CSV gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    private function loadPreview(): void
    {
        $path = $this->file->getPathname();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            $this->importErrors[] = 'File CSV kosong atau format tidak valid.';
            return;
        }

        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }

        $normalizedHeader = array_map('strtolower', array_map('trim', $header));
        $requiredColumns = ['name', 'email'];
        $missingColumns = array_diff($requiredColumns, $normalizedHeader);

        if (!empty($missingColumns)) {
            $this->importErrors[] = 'Kolom wajib tidak ditemukan: ' . implode(', ', $missingColumns);
            return;
        }

        $rows = [];
        while (count($rows) < 5 && ($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) continue;

            $rows[] = array_combine($normalizedHeader, array_slice($row, 0, count($normalizedHeader)));
        }
        fclose($handle);

        $this->preview = $rows;
        $this->totalRows = $this->countRows($path);
    }

    private function countRows(string $path): int
    {
        $count = 0;
        $last = '';
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return 0;
        }

        while (!feof($handle)) {
            $chunk = (string) fread($handle, 8192);
            $count += substr_count($chunk, "\n");

            if ($chunk !== '') {
                $last = $chunk[strlen($chunk) - 1];
            }
        }
        fclose($handle);

        if ($count > 0 && $last !== "\n") {
            $count++;
        }

        return max($count - 1, 0);
    }

    public function processData(): void
    {
        if (!$this->file) return;

        // Allow long-running imports (hashing thousands of rows takes minutes).
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
        $existingEmails = User::withTrashed()->pluck('email')
            ->map(fn ($email) => strtolower(trim($email)))
            ->flip()
            ->all();
        $roleIds = \Spatie\Permission\Models\Role::pluck('id', 'name')->all();
        $defaultRoleId = $roleIds['pengguna'] ?? null;

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
                $email = trim($data['email'] ?? '');

                if (empty($name) || empty($email)) {
                    $this->importErrors[] = "Baris {$rowNumber}: Nama dan email wajib diisi.";
                    $this->errorCount++;
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->importErrors[] = "Baris {$rowNumber}: Format email tidak valid ({$email}).";
                    $this->errorCount++;
                    continue;
                }

                if (isset($existingEmails[strtolower(trim($email))])) {
                    $this->importErrors[] = "Baris {$rowNumber}: Email sudah terdaftar ({$email}).";
                    $this->errorCount++;
                    continue;
                }

                $status = strtolower(trim($data['status'] ?? ''));
                if ($status === '') {
                    $status = 'Enable';
                }
                if (!in_array($status, ['Enable', 'Disable', 'enable', 'disable', 'active', 'resigned', 'resign'], true)) {
                    $this->importErrors[] = "Baris {$rowNumber}: Status tidak valid ({$status}). Gunakan Active atau Resigned.";
                    $this->errorCount++;
                    continue;
                }
                if (in_array($status, ['active', 'enable', 'resigned', 'resign'], true)) {
                    $status = in_array($status, ['active', 'enable'], true) ? 'Enable' : 'Disable';
                }

                $password = trim($data['password'] ?? '');
                if (empty($password)) {
                    $password = 'password';
                }

                $role = trim($data['role'] ?? '');
                $roleId = $roleIds[$role] ?? $defaultRoleId;

                $this->validRows[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'nik' => trim($data['nik'] ?? '') ?: null,
                        'status' => $status,
                        'role_id' => $roleId,
                    ],
                ];

                $this->importSuccess[] = [
                    'row' => $rowNumber,
                    'data' => [
                        'name' => $name,
                        'email' => $email,
                        'nik' => trim($data['nik'] ?? '') ?: '-',
                        'status' => $status,
                        'role' => $role ?: '-',
                    ],
                ];

                $existingEmails[strtolower(trim($email))] = true;

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

            return redirect()->route('admin.users.index');
        }

        // Allow long-running imports (hashing thousands of rows takes minutes).
        set_time_limit(0);

        $importedCount = 0;
        $skipped = [];

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($this->validRows as $validRow) {
                $data = $validRow['data'];

                try {
                    // Re-check against the DB (including soft-deleted users): a row may
                    // exist concurrently or have slipped through validation. A single
                    // duplicate must never abort the whole batch.
                    if (User::withTrashed()->where('email', $data['email'])->exists()) {
                        throw new \Exception("Email sudah terdaftar ({$data['email']}).");
                    }

                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        // Bcrypt cost 10 keeps import ~4x faster while staying OWASP-recommended.
                        'password' => Hash::make($data['password'], ['rounds' => 10]),
                        'nik' => $data['nik'],
                        'status' => $data['status'] ?? 'Enable',
                    ]);

                    $user->syncEmployeeLink();

                    if (!empty($data['role_id'])) {
                        $user->roles()->attach($data['role_id']);
                    }

                    $importedCount++;

                    $this->importedIds[] = $user->email;
                } catch (\Throwable $e) {
                    $skipped[] = "Baris {$validRow['row']}: " . $e->getMessage();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            $this->dismissConfirmImport();
            session()->flash('error', 'Kirim data gagal (perubahan dibatalkan): ' . $e->getMessage());

            return redirect()->route('admin.users.index');
        }

        $this->imported = $importedCount > 0;
        $this->showConfirmModal = false;

        if ($importedCount === 0) {
            // Nothing could be imported: surface the reason on the list page instead
            // of failing silently (which previously left the client stuck on the page).
            session()->flash('error', 'Tidak ada data yang berhasil diimpor: ' . ($skipped[0] ?? 'Periksa kembali file CSV.'));

            return redirect()->route('admin.users.index');
        }

        if (!empty($skipped)) {
            $this->importErrors = array_merge($this->importErrors, $skipped);
            $this->errorCount += count($skipped);
            $message = "Import selesai: {$importedCount} berhasil, " . count($skipped) . " gagal.";
        } else {
            $message = $this->errorCount > 0
                ? "Import selesai: {$importedCount} berhasil, {$this->errorCount} gagal."
                : "Import selesai: {$importedCount} data berhasil diimpor.";
        }

        ActivityLogger::log('import', "Mengimpor {$importedCount} data user" . (!empty($skipped) ? ' (' . count($skipped) . ' gagal)' : ''));

        session()->flash('success', $message);

        return redirect()->route('admin.users.index');
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

        $count = count($this->importedIds);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach (User::whereIn('email', $this->importedIds)->get() as $user) {
                $user->roles()->detach();
                $user->forceDelete();
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

        ActivityLogger::log('delete', "Membatalkan import: menghapus {$count} data user");
        $this->dispatch('show-toast', message: "Import dibatalkan: {$count} data user dihapus.", type: 'success');
        $this->resetImport();
    }

    public function getRoleList(): array
    {
        return \Spatie\Permission\Models\Role::pluck('name')->toArray();
    }

    public function render()
    {
        return view('livewire.users.import-csv');
    }
}
