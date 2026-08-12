<?php

namespace App\Livewire\Admin\Assets;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Site;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateForm extends Component
{
    use WithFileUploads;

    public string $kategori = '';
    public string $brand = '';
    public string $tipe = '';
    public string $namaPerangkat = '';
    public string $noSerial = '';
    public string $spesifikasi = '';
    public $foto = null;
    public string $noAsset = '';
    public string $operatingUnit = '';
    public string $siteLocationAsset = '';
    public ?string $assignedEmployeeId = null;

    protected function rules(): array
    {
        return [
            'kategori' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'tipe' => 'required|string|max:255',
            'namaPerangkat' => 'required|string|max:255',
            'noSerial' => 'nullable|string|max:255',
            'spesifikasi' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'noAsset' => 'required|string|max:255|unique:assets,no_asset',
            'operatingUnit' => 'nullable|string|max:255',
            'siteLocationAsset' => 'nullable|string|max:255',
            'assignedEmployeeId' => 'nullable|exists:employees,nik',
        ];
    }

    protected function messages(): array
    {
        return [
            'kategori.required' => 'Kategori wajib diisi.',
            'brand.required' => 'Brand wajib diisi.',
            'tipe.required' => 'Tipe wajib diisi.',
            'namaPerangkat.required' => 'Nama Perangkat wajib diisi.',
            'noAsset.required' => 'No Asset wajib diisi.',
            'noAsset.unique' => 'No Asset sudah terdaftar.',
            'assignedEmployeeId.exists' => 'Pengguna tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }

    public function save(): void
    {
        try {
            $this->validate();

            $fotoPath = null;
            if ($this->foto) {
                $fotoPath = $this->foto->store('assets', 'public');
            }

            Asset::create([
                'kategori' => $this->kategori,
                'brand' => $this->brand,
                'tipe' => $this->tipe,
                'nama_perangkat' => $this->namaPerangkat,
                'no_serial' => $this->noSerial ?: null,
                'spesifikasi' => $this->spesifikasi ?: null,
                'foto' => $fotoPath,
                'no_asset' => $this->noAsset,
                'status' => $this->assignedEmployeeId ? 'active' : 'inactive',
                'operating_unit' => $this->operatingUnit ?: null,
                'site_location_asset' => $this->siteLocationAsset ?: null,
                'assigned_employee_id' => $this->assignedEmployeeId,
            ]);

            ActivityLogger::log('create', "Menambahkan asset baru: {$this->noAsset} - {$this->namaPerangkat}", 'App\Models\Asset', null, ['no_asset' => $this->noAsset]);
            $this->dispatch('asset-created');
            $this->reset();
            $this->reset('foto');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error', errors: $e->errors());
        }
    }

    public function render()
    {
        return view('livewire.admin.assets.create-form', [
            'sites' => Site::orderBy('site')->get(),
            'employees' => Employee::where('status', Employee::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }
}
