<?php

namespace App\Livewire\Admin\Pengembalian;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\FormPengembalian;
use App\Models\FormPengembalianItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateForm extends Component
{
    public ?string $penggunaId = null;

    public string $penggunaSearch = '';

    public array $penggunaResults = [];

    public bool $showPenggunaDropdown = false;

    public string $penggunaName = '';

    public string $penggunaNik = '';

    public string $penggunaEmail = '';

    public array $availableAssets = [];

    public array $selectedAssets = [];

    public string $tanggalPengembalian = '';

    public string $kondisi = '';

    public string $kelengkapan = '';

    public string $notes = '';

    public function mount(?int $employee = null): void
    {
        $employeeId = $employee ?? request('employee');

        if ($employeeId) {
            $this->selectPengguna((string) $employeeId);
        }
    }

    protected function rules(): array
    {
        return [
            'penggunaId' => 'required|exists:employees,nik',
            'selectedAssets' => 'required|array|min:1',
            'tanggalPengembalian' => 'nullable|date',
            'kondisi' => 'nullable|in:baik,rusak,hilang',
            'kelengkapan' => 'nullable|in:lengkap,tidak_lengkap',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'penggunaId.required' => 'Pilih employee yang mengembalikan asset.',
            'penggunaId.exists' => 'Employee tidak ditemukan.',
            'selectedAssets.required' => 'Pilih minimal satu asset.',
            'selectedAssets.min' => 'Pilih minimal satu asset.',
            'tanggalPengembalian.date' => 'Format tanggal tidak valid.',
            'kondisi.in' => 'Kondisi harus Baik, Rusak, atau Hilang.',
            'kelengkapan.in' => 'Kelengkapan harus Lengkap atau Tidak Lengkap.',
        ];
    }

    public function searchPengguna(): void
    {
        if (strlen($this->penggunaSearch) < 2) {
            $this->penggunaResults = [];
            $this->showPenggunaDropdown = false;

            return;
        }

        $this->penggunaResults = Employee::where('status', Employee::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->penggunaSearch}%")
                    ->orWhere('nik', 'like', "%{$this->penggunaSearch}%")
                    ->orWhere('email', 'like', "%{$this->penggunaSearch}%");
            })
            ->limit(10)
            ->get()
            ->toArray();

        $this->showPenggunaDropdown = strlen($this->penggunaSearch) >= 2;
    }

    public function selectPengguna(string $nik): void
    {
        $employee = Employee::find($nik);

        if (! $employee) {
            return;
        }

        $this->penggunaId = $employee->nik;
        $this->penggunaName = $employee->name;
        $this->penggunaNik = $employee->nik ?? '';
        $this->penggunaEmail = $employee->email ?? '';
        $this->penggunaSearch = $employee->name;
        $this->showPenggunaDropdown = false;
        $this->selectedAssets = [];

        $this->availableAssets = $employee->assignedAssets()
            ->get(['id', 'no_asset', 'nama_perangkat', 'brand', 'tipe', 'no_serial'])
            ->toArray();
    }

    public function clearPengguna(): void
    {
        $this->penggunaId = null;
        $this->penggunaName = '';
        $this->penggunaNik = '';
        $this->penggunaEmail = '';
        $this->penggunaSearch = '';
        $this->availableAssets = [];
        $this->selectedAssets = [];
    }

    public function generateNomorForm(): string
    {
        $today = now()->format('dmY');

        $sequence = FormPengembalian::withTrashed()
            ->where('nomor_form', 'like', "%/PNG/{$today}")
            ->count() + 1;

        while (FormPengembalian::withTrashed()->where('nomor_form', "{$sequence}/PNG/{$today}")->exists()) {
            $sequence++;
        }

        return str_pad($sequence, 3, '0', STR_PAD_LEFT) . "/PNG/{$today}";
    }

    public function submit(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $form = FormPengembalian::create([
                'nomor_form' => $this->generateNomorForm(),
                'teknisi_id' => Auth::id(),
                'pengguna_employee_id' => $this->penggunaId,
                'tanggal_pengembalian' => $this->tanggalPengembalian ?: null,
                'kondisi' => $this->kondisi ?: null,
                'kelengkapan' => $this->kelengkapan ?: null,
                'notes' => $this->notes ?: null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            foreach ($this->selectedAssets as $assetId) {
                FormPengembalianItem::create([
                    'form_pengembalian_id' => $form->id,
                    'asset_id' => (int) $assetId,
                ]);
            }

            Asset::whereIn('id', $this->selectedAssets)
                ->update(['assigned_employee_id' => null, 'status' => 'inactive']);

            ActivityLogger::log(
                'submit',
                "Mengembalikan " . count($this->selectedAssets) . " asset dari {$this->penggunaName}: {$form->nomor_form}",
                'App\Models\FormPengembalian',
                $form->id
            );

            DB::commit();

            $this->redirect(route('admin.pengembalian.index'));

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->addError('submit', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.pengembalian.create-form');
    }
}
