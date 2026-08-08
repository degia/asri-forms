<?php

namespace App\Livewire\Admin\Sites;

use App\Helpers\ActivityLogger;
use App\Models\Site;
use Livewire\Component;

class CreateForm extends Component
{
    public string $idSite = '';
    public string $site = '';
    public string $buss = '';
    public string $idCorp = '';
    public string $country = '';
    public string $provincy = '';
    public string $city = '';
    public string $address = '';
    public string $urlMaps = '';

    protected function rules(): array
    {
        return [
            'idSite' => 'required|string|max:3|unique:sites,id_site',
            'site' => 'required|string|max:255',
            'buss' => 'nullable|string|max:1',
            'idCorp' => 'nullable|string|max:3',
            'country' => 'nullable|string|max:255',
            'provincy' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'urlMaps' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'idSite.required' => 'ID Site wajib diisi.',
            'idSite.unique' => 'ID Site sudah terdaftar.',
            'site.required' => 'Nama Site wajib diisi.',
        ];
    }

    public function save(): void
    {
        try {
            $this->validate();

            Site::create([
                'id_site' => $this->idSite,
                'site' => $this->site,
                'buss' => $this->buss ?: null,
                'id_corp' => $this->idCorp ?: null,
                'country' => $this->country ?: null,
                'provincy' => $this->provincy ?: null,
                'city' => $this->city ?: null,
                'address' => $this->address ?: null,
                'url_maps' => $this->urlMaps ?: null,
            ]);

            ActivityLogger::log('create', "Menambahkan site baru: {$this->idSite} - {$this->site}", 'App\Models\Site', $this->idSite);

            $this->dispatch('show-toast', message: "Site {$this->idSite} - {$this->site} berhasil disimpan.", type: 'success');
            $this->dispatch('site-created');
            $this->reset();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error', errors: $e->errors());
            $this->dispatch('show-toast', message: 'Data gagal disimpan: ' . (collect($e->errors())->flatten()->first() ?? 'Periksa kembali isian form.'), type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('show-toast', message: 'Data gagal disimpan: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.sites.create-form');
    }
}
