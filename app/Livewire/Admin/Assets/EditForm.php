<?php

namespace App\Livewire\Admin\Assets;

use App\Helpers\ActivityLogger;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditForm extends Component
{
    use WithFileUploads;

    public ?Asset $assetModel = null;
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

    public function mount(int $id): void
    {
        $asset = Asset::findOrFail($id);
        $this->assetModel = $asset;
        $this->kategori = $asset->kategori;
        $this->brand = $asset->brand;
        $this->tipe = $asset->tipe;
        $this->namaPerangkat = $asset->nama_perangkat;
        $this->noSerial = $asset->no_serial ?? '';
        $this->spesifikasi = $asset->spesifikasi ?? '';
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
            'spesifikasi' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
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
            'foto.image' => 'File harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }

    public function update(): void
    {
        try {
            $this->validate();

            if ($this->foto) {
                if ($this->assetModel->foto) {
                    Storage::disk('public')->delete($this->assetModel->foto);
                }
                $fotoPath = $this->foto->store('assets', 'public');
            } else {
                $fotoPath = $this->assetModel->foto;
            }

            $this->assetModel->update([
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
                'assigned_employee_id' => $this->assignedEmployeeId ?: null,
            ]);

            ActivityLogger::log('update', "Mengubah asset: {$this->noAsset} - {$this->namaPerangkat}", 'App\Models\Asset', $this->assetModel?->id, ['no_asset' => $this->noAsset]);
            $this->dispatch('asset-updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error', errors: $e->errors());
        }
    }

    public function removeFoto(): void
    {
        if ($this->assetModel?->foto) {
            Storage::disk('public')->delete($this->assetModel->foto);
            $this->assetModel->update(['foto' => null]);
            $this->foto = null;
            $this->dispatch('asset-updated');
        }
    }

    public function render()
    {
        return view('livewire.admin.assets.edit-form', [
            'sites' => Site::orderBy('site')->get(),
            'employees' => Employee::where('status', Employee::STATUS_ACTIVE)->orderBy('name')->get(),
        ]);
    }
}
