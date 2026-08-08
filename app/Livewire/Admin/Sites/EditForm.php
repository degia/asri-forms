<?php

namespace App\Livewire\Admin\Sites;

use App\Helpers\ActivityLogger;
use App\Models\Site;
use Livewire\Component;

class EditForm extends Component
{
    public ?Site $siteModel = null;
    public string $idSite = '';
    public string $site = '';
    public string $buss = '';
    public string $idCorp = '';
    public string $country = '';
    public string $provincy = '';
    public string $city = '';
    public string $address = '';
    public string $urlMaps = '';

    public function mount(string $idSite): void
    {
        $site = Site::findOrFail($idSite);
        $this->siteModel = $site;
        $this->idSite = $site->id_site ?? '';
        $this->site = $site->site;
        $this->buss = $site->buss ?? '';
        $this->idCorp = $site->id_corp ?? '';
        $this->country = $site->country ?? '';
        $this->provincy = $site->provincy ?? '';
        $this->city = $site->city ?? '';
        $this->address = $site->address ?? '';
        $this->urlMaps = $site->url_maps ?? '';
    }

    protected function rules(): array
    {
        return [
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
            'site.required' => 'Nama Site wajib diisi.',
        ];
    }

    public function update(): void
    {
        try {
            $this->validate();

            $this->siteModel->update([
                'site' => $this->site,
                'buss' => $this->buss ?: null,
                'id_corp' => $this->idCorp ?: null,
                'country' => $this->country ?: null,
                'provincy' => $this->provincy ?: null,
                'city' => $this->city ?: null,
                'address' => $this->address ?: null,
                'url_maps' => $this->urlMaps ?: null,
            ]);

            ActivityLogger::log('update', "Mengubah site: {$this->idSite} - {$this->site}", 'App\Models\Site', $this->idSite);
            $this->dispatch('site-updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error', errors: $e->errors());
        }
    }

    public function render()
    {
        return view('livewire.admin.sites.edit-form');
    }
}
