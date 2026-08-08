<?php

namespace App\Livewire\Assets;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Site;
use Livewire\Component;

class Edit extends Component
{
    public ?Asset $assetModel = null;
    public string $kategori = '';
    public string $brand = '';
    public string $tipe = '';
    public string $namaPerangkat = '';
    public string $noSerial = '';
    public string $noAsset = '';
    public string $operatingUnit = '';
    public string $siteLocationAsset = '';
    public ?string $assignedEmployeeId = null;

    public function mount(int $id): void
    {
        $asset = Asset::findOrFail($id);
        $this->assetModel = $asset;
        $this->kategori = $asset->kategori;
        $this->brand = $asset->brand;
        $this->tipe = $asset->tipe;
        $this->namaPerangkat = $asset->nama_perangkat;
        $this->noSerial = $asset->no_serial ?? '';
        $this->noAsset = $asset->no_asset;
        $this->operatingUnit = $asset->operating_unit ?? '';
        $this->siteLocationAsset = $asset->site_location_asset ?? '';
        $this->assignedEmployeeId = $asset->assigned_employee_id;
    }

    protected function rules(): array
    {
        return [
            'kategori' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'tipe' => 'required|string|max:255',
            'namaPerangkat' => 'required|string|max:255',
            'noSerial' => 'nullable|string|max:255',
            'noAsset' => 'required|string|max:255|unique:assets,no_asset,' . $this->assetModel?->id,
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
        ];
    }

    public function update(): void
    {
        try {
            $this->validate();

            $this->assetModel->update([
                'kategori' => $this->kategori,
                'brand' => $this->brand,
                'tipe' => $this->tipe,
                'nama_perangkat' => $this->namaPerangkat,
                'no_serial' => $this->noSerial ?: null,
                'no_asset' => $this->noAsset,
                'status' => $this->assignedEmployeeId ? 'active' : 'inactive',
                'operating_unit' => $this->operatingUnit ?: null,
                'site_location_asset' => $this->siteLocationAsset ?: null,
                'assigned_employee_id' => $this->assignedEmployeeId,
            ]);

            ActivityLogger::log('update', "Mengubah asset: {$this->noAsset} - {$this->namaPerangkat}", 'App\Models\Asset', $this->assetModel?->id, ['no_asset' => $this->noAsset]);
            $this->dispatch('asset-updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error', errors: $e->errors());
        }
    }

    public function render()
    {
        return view('livewire.assets.edit', [
            'sites' => Site::orderBy('site')->get(),
            'employees' => Employee::where('status', Employee::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }
}
