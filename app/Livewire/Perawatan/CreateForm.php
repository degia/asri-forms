<?php

namespace App\Livewire\Perawatan;

use App\Helpers\ActivityLogger;
use App\Enums\FormStatus;
use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\Employee;
use App\Models\FormApproval;
use App\Models\FormPerawatan;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateForm extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    public const TOTAL_STEPS = 8;

    public array $stepTitles = [
        1 => 'Info Pengguna',
        2 => 'Info Perangkat',
        3 => 'Perawatan Hardware',
        4 => 'Perawatan Aplikasi',
        5 => 'Perawatan Operating System',
        6 => 'Kondisi Setelah Perawatan',
        7 => 'Catatan Tambahan',
        8 => 'Review & Submit',
    ];

    // Step 1: Info Pengguna
    public ?string $penggunaId = null;

    public string $teknisiName = '';

    public string $teknisiNik = '';

    public string $teknisiSite = '';

    public string $penggunaName = '';

    public string $penggunaNik = '';

    public string $penggunaEmail = '';

    // Step 2: Info Perangkat
    public ?int $assetId = null;

    public string $kategori = '';

    public string $brand = '';

    public string $tipe = '';

    public string $namaPerangkat = '';

    public string $noSerial = '';

    public string $noAsset = '';

    public string $siteLocation = '';

    public string $locationDetail = '';

    // Steps 3-5: Checklist items
    public array $hardwareItems = [];

    public array $aplikasiItems = [];

    public array $osItems = [];

    // Step 6: Kondisi Setelah Perawatan
    public string $kondisiAkhir = '';

    public string $kondisiAkhirNotes = '';

    // Step 7: Catatan
    public string $notes = '';

    public bool $barcodeFisik = false;

    // Draft
    public ?int $formId = null;

    public bool $isDraft = false;

    public string $nomorForm = '';

    // Search - Pengguna
    public string $penggunaSearch = '';

    public array $penggunaResults = [];

    public bool $showPenggunaDropdown = false;

    // Create new pengguna
    public bool $showCreatePengguna = false;

    public string $newPenggunaName = '';

    public string $newPenggunaNik = '';

    public string $newPenggunaSite = '';

    public string $newPenggunaEmail = '';

    // Email search for new pengguna
    public array $emailSearchResults = [];

    public bool $showEmailDropdown = false;

    // Add user popup
    public bool $showAddUserPopup = false;

    public string $addUserPassword = 'password';

    public string $addUserRole = 'pengguna';

    // Search - Asset
    public string $assetSearch = '';

    public array $assetResults = [];

    public bool $showAssetDropdown = false;

    // Sites
    public array $sites = [];

    // Create new asset
    public bool $showCreateAsset = false;

    public string $newAssetNoAsset = '';

    public string $newAssetKategori = '';

    public string $newAssetBrand = '';

    public string $newAssetTipe = '';

    public string $newAssetNamaPerangkat = '';

    public string $newAssetNoSerial = '';

    // Photo uploads
    public array $itemPhotos = [];

    protected $listeners = [
        'autosave' => 'saveDraft',
    ];

    protected function rules(): array
    {
        return [
            'penggunaId' => 'required|exists:employees,nik',
            'assetId' => 'required|exists:assets,id',
            'hardwareItems.*.status' => 'nullable|in:baik,tidak_baik',
            'hardwareItems.*.keterangan' => 'nullable|string|max:1000',
            'hardwareItems.*.full_charge_capacity' => 'nullable|integer|min:0',
            'hardwareItems.*.design_capacity' => 'nullable|integer|min:0',
            'aplikasiItems.*.status' => 'nullable|in:baik,tidak_baik',
            'aplikasiItems.*.keterangan' => 'nullable|string|max:1000',
            'osItems.*.status' => 'nullable|in:baik,tidak_baik',
            'osItems.*.keterangan' => 'nullable|string|max:1000',
            'kondisiAkhir' => 'required|in:good,fair,critical,poor',
            'kondisiAkhirNotes' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'barcodeFisik' => 'boolean',
        ];
    }

    public function mount(?int $formId = null): void
    {
        $user = Auth::user();
        $this->teknisiName = $user->name;
        $this->teknisiNik = $user->nik ?? '';
        $this->teknisiSite = $user->siteName ?? '';

        $this->sites = Site::orderBy('id_site')->get()->toArray();

        if (request('formId')) {
            $formId = request('formId');
        }

        $this->loadChecklistTemplates();

        if ($formId) {
            $this->loadFormData($formId);
        }
    }

    private function loadFormData(int $formId): void
    {
        $user = Auth::user();
        if (!$user || !($user->hasRole('admin') || $user->hasRole('teknisi'))) return;

        $form = FormPerawatan::with(['items', 'pengguna', 'asset'])->find($formId);
        if (! $form || ! in_array($form->status, [FormStatus::Draft->value, FormStatus::Submitted->value, FormStatus::Diketahui->value])) {
            return;
        }

        $this->formId = $form->id;
        $this->nomorForm = $form->nomor_form;
        $this->isDraft = true;

        $this->penggunaId = $form->pengguna_employee_id;
        if ($form->pengguna) {
            $this->penggunaName = $form->pengguna->name;
            $this->penggunaNik = $form->pengguna->nik ?? '';
            $this->penggunaEmail = $form->pengguna->email ?? '';
        }

        $this->assetId = $form->asset_id;
        if ($form->asset) {
            $this->kategori = $form->asset->kategori ?? '';
            $this->brand = $form->asset->brand ?? '';
            $this->tipe = $form->asset->tipe ?? '';
            $this->namaPerangkat = $form->asset->nama_perangkat ?? '';
            $this->noSerial = $form->asset->no_serial ?? '';
            $this->noAsset = $form->asset->no_asset ?? '';
            $this->assetSearch = $form->asset->no_asset ?? '';
        }

        $this->siteLocation = $form->site_location ?? '';
        $this->locationDetail = $form->location_detail ?? '';
        $this->kondisiAkhir = $form->kondisi_akhir ?? '';
        $this->kondisiAkhirNotes = $form->kondisi_akhir_notes ?? '';
        $this->barcodeFisik = (bool) ($form->barcode_fisik ?? false);
        $this->notes = $form->notes ?? '';

        foreach ($form->items as $item) {
            $category = $item->category;
            if ($category === 'hardware' && isset($this->hardwareItems[$item->sort_order])) {
                $this->hardwareItems[$item->sort_order]['status'] = $item->status;
                $this->hardwareItems[$item->sort_order]['keterangan'] = $item->keterangan ?? '';
                $this->hardwareItems[$item->sort_order]['full_charge_capacity'] = $item->full_charge_capacity;
                $this->hardwareItems[$item->sort_order]['design_capacity'] = $item->design_capacity;
            } elseif ($category === 'aplikasi' && isset($this->aplikasiItems[$item->sort_order])) {
                $this->aplikasiItems[$item->sort_order]['status'] = $item->status;
                $this->aplikasiItems[$item->sort_order]['keterangan'] = $item->keterangan ?? '';
            } elseif ($category === 'operating_system' && isset($this->osItems[$item->sort_order])) {
                $this->osItems[$item->sort_order]['status'] = $item->status;
                $this->osItems[$item->sort_order]['keterangan'] = $item->keterangan ?? '';
            }
        }
    }

    private function loadChecklistTemplates(): void
    {
        $hwTemplate = ChecklistTemplate::where('form_type', 'perawatan')
            ->where('category', 'hardware')
            ->where('is_active', true)
            ->with('items')
            ->first();

        if ($hwTemplate) {
            $this->hardwareItems = $hwTemplate->items->sortBy('sort_order')->map(fn ($item) => [
                'template_item_id' => $item->id,
                'name' => $item->name,
                'status' => null,
                'keterangan' => '',
                'sort_order' => $item->sort_order,
                'full_charge_capacity' => null,
                'design_capacity' => null,
            ])->values()->toArray();
        }

        $appTemplate = ChecklistTemplate::where('form_type', 'perawatan')
            ->where('category', 'aplikasi')
            ->where('is_active', true)
            ->with('items')
            ->first();

        if ($appTemplate) {
            $this->aplikasiItems = $appTemplate->items->sortBy('sort_order')->map(fn ($item) => [
                'template_item_id' => $item->id,
                'name' => $item->name,
                'status' => null,
                'keterangan' => '',
                'sort_order' => $item->sort_order,
            ])->values()->toArray();
        }

        $osTemplate = ChecklistTemplate::where('form_type', 'perawatan')
            ->where('category', 'operating_system')
            ->where('is_active', true)
            ->with('items')
            ->first();

        if ($osTemplate) {
            $this->osItems = $osTemplate->items->sortBy('sort_order')->map(fn ($item) => [
                'template_item_id' => $item->id,
                'name' => $item->name,
                'status' => null,
                'keterangan' => '',
                'sort_order' => $item->sort_order,
            ])->values()->toArray();
        }
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
        if ($employee) {
            $this->penggunaId = $employee->nik;
            $this->penggunaName = $employee->name;
            $this->penggunaNik = $employee->nik ?? '';
            $this->penggunaEmail = $employee->email ?? '';
            $this->penggunaSearch = $employee->name;
            $this->showPenggunaDropdown = false;
        }
    }

    public function clearPengguna(): void
    {
        $this->penggunaId = null;
        $this->penggunaName = '';
        $this->penggunaNik = '';
        $this->penggunaEmail = '';
        $this->penggunaSearch = '';
        $this->showCreatePengguna = false;
        $this->resetNewPenggunaFields();
    }

    public function openCreatePengguna(): void
    {
        $this->showCreatePengguna = true;
        $this->showPenggunaDropdown = false;
        $this->newPenggunaName = $this->penggunaSearch;
    }

    public function closeCreatePengguna(): void
    {
        $this->showCreatePengguna = false;
        $this->resetNewPenggunaFields();
    }

    public function createPengguna(): void
    {
        $usedByName = $this->newPenggunaEmailUsedByEmployee;
        if ($usedByName) {
            $this->dispatch('show-toast', message: "Email sudah terpakai pada employee \"{$usedByName}\".", type: 'error');

            return;
        }

        $this->validate([
            'newPenggunaName' => 'required|string|max:255',
            'newPenggunaEmail' => 'nullable|email|max:255|exists:users,email|unique:employees,email',
            'newPenggunaNik' => 'nullable|string|max:50|unique:employees,nik',
            'newPenggunaSite' => 'nullable|string|max:255|exists:sites,id_site',
        ], [
            'newPenggunaEmail.exists' => 'Email harus terdaftar sebagai akun user terlebih dahulu.',
            'newPenggunaEmail.unique' => 'Email sudah terdaftar pada employee lain.',
            'newPenggunaNik.unique' => 'NIK sudah terdaftar pada employee lain.',
            'newPenggunaSite.exists' => 'Site yang dipilih tidak valid.',
        ]);

        $employee = Employee::create([
            'name' => $this->newPenggunaName,
            'email' => $this->newPenggunaEmail ?: null,
            'nik' => $this->newPenggunaNik ?: null,
            'site' => $this->newPenggunaSite ?: null,
            'status' => Employee::STATUS_ACTIVE,
            'akun_login' => $this->newPenggunaEmail ? 'Connect' : 'No Access',
        ]);

        if ($this->newPenggunaEmail) {
            $user = User::where('email', $this->newPenggunaEmail)->first();
            if ($user) {
                $user->update(['nik' => $employee->nik]);
            }
        }

        $this->penggunaId = $employee->nik;
        $this->penggunaName = $employee->name;
        $this->penggunaNik = $employee->nik ?? '';
        $this->penggunaEmail = $employee->email ?? '';
        $this->penggunaSearch = $employee->name;
        $this->showPenggunaDropdown = false;
        $this->showCreatePengguna = false;

        $this->resetNewPenggunaFields();

        $this->dispatch('penggunaCreated', name: $employee->name);
    }

    public function getIsNewPenggunaEmailUnregisteredProperty(): bool
    {
        $email = trim($this->newPenggunaEmail);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return ! User::withTrashed()->where('email', $email)->exists();
    }

    public function getNewPenggunaEmailUsedByEmployeeProperty(): ?string
    {
        $email = trim($this->newPenggunaEmail);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return Employee::where('email', $email)->value('name');
    }

    public function searchNewPenggunaEmail(): void
    {
        $this->showEmailDropdown = false;

        if (strlen(trim($this->newPenggunaEmail)) < 2) {
            $this->emailSearchResults = [];

            return;
        }

        $usedEmails = User::whereNotNull('nik')->pluck('email');

        $this->emailSearchResults = User::where('email', 'like', '%'.trim($this->newPenggunaEmail).'%')
            ->whereNotIn('email', $usedEmails)
            ->limit(10)
            ->get(['email', 'name'])
            ->toArray();

        $this->showEmailDropdown = count($this->emailSearchResults) > 0;
    }

    public function selectNewPenggunaEmail(string $email): void
    {
        $this->newPenggunaEmail = $email;
        $this->emailSearchResults = [];
        $this->showEmailDropdown = false;
    }

    public function openAddUserPopup(): void
    {
        if (! filter_var(trim($this->newPenggunaEmail), FILTER_VALIDATE_EMAIL)) {
            $this->dispatch('show-toast', message: 'Isi alamat email terlebih dahulu.', type: 'error');

            return;
        }

        $this->addUserPassword = 'password';
        $this->addUserRole = 'pengguna';
        $this->showAddUserPopup = true;
    }

    public function closeAddUserPopup(): void
    {
        $this->showAddUserPopup = false;
    }

    public function getRoleList(): array
    {
        return \Spatie\Permission\Models\Role::pluck('name')->toArray();
    }

    public function saveAddUser(): void
    {
        $this->validate([
            'newPenggunaName' => 'required|string|max:255',
            'newPenggunaEmail' => 'required|email|max:255',
            'newPenggunaNik' => 'nullable|string|max:50|unique:users,nik|unique:employees,nik',
            'newPenggunaSite' => 'nullable|string|max:255|exists:sites,id_site',
            'addUserPassword' => 'required|string|min:6',
            'addUserRole' => 'required|exists:roles,name',
        ], [
            'newPenggunaName.required' => 'Nama wajib diisi.',
            'newPenggunaNik.unique' => 'NIK sudah terdaftar pada employee atau akun user lain.',
            'newPenggunaSite.exists' => 'Site yang dipilih tidak valid.',
            'addUserPassword.required' => 'Password wajib diisi.',
            'addUserPassword.min' => 'Password minimal 6 karakter.',
            'addUserRole.exists' => 'Role tidak valid.',
        ]);

        $email = trim($this->newPenggunaEmail);

        if (User::withTrashed()->where('email', $email)->exists()) {
            $this->dispatch('show-toast', message: 'Email sudah terdaftar sebagai akun user.', type: 'error');

            return;
        }

        $employee = Employee::create([
            'name' => trim($this->newPenggunaName),
            'nik' => $this->newPenggunaNik ?: null,
            'site' => $this->newPenggunaSite ?: null,
            'status' => Employee::STATUS_ACTIVE,
            'akun_login' => 'Connect',
        ]);

        $user = User::create([
            'name' => $employee->name,
            'email' => $email,
            'password' => $this->addUserPassword,
            'nik' => $employee->nik,
            'status' => User::STATUS_ACTIVE,
        ]);

        $employee->update(['email' => $user->email]);

        $user->assignRole($this->addUserRole);

        $this->penggunaId = $employee->nik;
        $this->penggunaName = $employee->name;
        $this->penggunaNik = $employee->nik;
        $this->penggunaEmail = $employee->email;
        $this->penggunaSearch = $employee->name;
        $this->showPenggunaDropdown = false;
        $this->showCreatePengguna = false;
        $this->showAddUserPopup = false;

        $this->resetNewPenggunaFields();

        ActivityLogger::log('create', "Membuat akun user baru dari form perawatan: {$email}", 'App\Models\User', $email);

        $this->dispatch('penggunaCreated', name: $employee->name);
        $this->dispatch('show-toast', message: "Akun user {$email} berhasil dibuat. Password default: {$this->addUserPassword}.", type: 'success');
    }

    public function closePenggunaCredentials(): void
    {
        $this->showCreatePengguna = false;
    }

    private function resetNewPenggunaFields(): void
    {
        $this->newPenggunaName = '';
        $this->newPenggunaNik = '';
        $this->newPenggunaSite = '';
        $this->newPenggunaEmail = '';
    }

    public function searchAsset(): void
    {
        if (strlen($this->assetSearch) < 2) {
            $this->assetResults = [];
            $this->showAssetDropdown = false;

            return;
        }

        $query = Asset::where('no_asset', 'like', "%{$this->assetSearch}%")
            ->orWhere('nama_perangkat', 'like', "%{$this->assetSearch}%")
            ->orWhere('brand', 'like', "%{$this->assetSearch}%")
            ->orWhere('tipe', 'like', "%{$this->assetSearch}%")
            ->orWhere('no_serial', 'like', "%{$this->assetSearch}%");

        $user = Auth::user();
        if ($user && ! $user->hasPermissionTo('view-all-forms') && $user->hasPermissionTo('view-assigned-forms')) {
            $query->where('assigned_employee_id', $user->nik);
        }

        $this->assetResults = $query->limit(10)->get()->toArray();

        $this->showAssetDropdown = strlen($this->assetSearch) >= 2;
    }

    public function selectAsset(int $assetId): void
    {
        $asset = Asset::find($assetId);
        if ($asset) {
            $this->assetId = $assetId;
            $this->kategori = $asset->kategori ?? '';
            $this->brand = $asset->brand ?? '';
            $this->tipe = $asset->tipe ?? '';
            $this->namaPerangkat = $asset->nama_perangkat ?? '';
            $this->noSerial = $asset->no_serial ?? '';
            $this->noAsset = $asset->no_asset;
            $this->assetSearch = $asset->no_asset;
            $this->showAssetDropdown = false;
        }
    }

    public function clearAsset(): void
    {
        $this->assetId = null;
        $this->kategori = '';
        $this->brand = '';
        $this->tipe = '';
        $this->namaPerangkat = '';
        $this->noSerial = '';
        $this->noAsset = '';
        $this->assetSearch = '';
        $this->showAssetDropdown = false;
        $this->showCreateAsset = false;
        $this->resetNewAssetFields();
    }

    public function openCreateAsset(): void
    {
        $this->showCreateAsset = true;
        $this->showAssetDropdown = false;
        $this->newAssetNoAsset = $this->assetSearch;
    }

    public function closeCreateAsset(): void
    {
        $this->showCreateAsset = false;
        $this->resetNewAssetFields();
    }

    public function createAsset(): void
    {
        $this->validate([
            'newAssetNoAsset' => 'required|string|max:255|unique:assets,no_asset',
            'newAssetKategori' => 'nullable|string|max:255',
            'newAssetBrand' => 'nullable|string|max:255',
            'newAssetTipe' => 'nullable|string|max:255',
            'newAssetNamaPerangkat' => 'nullable|string|max:255',
            'newAssetNoSerial' => 'nullable|string|max:255',
        ]);

        $asset = Asset::create([
            'no_asset' => $this->newAssetNoAsset,
            'kategori' => $this->newAssetKategori,
            'brand' => $this->newAssetBrand,
            'tipe' => $this->newAssetTipe,
            'nama_perangkat' => $this->newAssetNamaPerangkat,
            'no_serial' => $this->newAssetNoSerial,
            'status' => 'active',
        ]);

        $this->assetId = $asset->id;
        $this->kategori = $asset->kategori ?? '';
        $this->brand = $asset->brand ?? '';
        $this->tipe = $asset->tipe ?? '';
        $this->namaPerangkat = $asset->nama_perangkat ?? '';
        $this->noSerial = $asset->no_serial ?? '';
        $this->noAsset = $asset->no_asset;
        $this->assetSearch = $asset->no_asset;
        $this->showAssetDropdown = false;
        $this->showCreateAsset = false;
        $this->resetNewAssetFields();

        $this->dispatch('assetCreated', name: $asset->nama_perangkat);
    }

    private function resetNewAssetFields(): void
    {
        $this->newAssetNoAsset = '';
        $this->newAssetKategori = '';
        $this->newAssetBrand = '';
        $this->newAssetTipe = '';
        $this->newAssetNamaPerangkat = '';
        $this->newAssetNoSerial = '';
    }

    public function generateNomorForm(): string
    {
        $today = now()->format('dmY');
        $assetCode = $this->noAsset ?? 'XXXX';
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $assetCode);

        $count = FormPerawatan::where('nomor_form', 'like', "%/PWT/{$escaped}/{$today}")
            ->count();

        $sequence = $count + 1;
        $seqStr = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        while (FormPerawatan::where('nomor_form', "{$seqStr}/PWT/{$assetCode}/{$today}")->exists()) {
            $sequence++;
            $seqStr = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        }

        return "{$seqStr}/PWT/{$assetCode}/{$today}";
    }

    public function nextStep(): void
    {
        if ($this->currentStep < self::TOTAL_STEPS) {
            $this->currentStep++;
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= self::TOTAL_STEPS) {
            $this->currentStep = $this->currentStep === $step ? 0 : $step;
        }
    }

    public function toggleItemStatus(string $list, int $index, string $status): void
    {
        if (! isset($this->$list[$index])) {
            return;
        }

        $current = $this->$list[$index]['status'] ?? null;
        $this->$list[$index]['status'] = $current === $status ? null : $status;
    }

    public function saveDraft(): void
    {
        $data = $this->getFormData();
        $data['status'] = FormStatus::Draft->value;

        if ($this->formId) {
            $form = FormPerawatan::find($this->formId);
            if ($form) {
                $form->update($data);
                $this->syncItems($form);
                ActivityLogger::log('create', "Menyimpan draft form Perawatan: {$this->noAsset}", 'App\Models\FormPerawatan', $form->id);
                $this->dispatch('draftSaved');
                $this->redirect(route('forms.search'));

                return;
            }
        }

        if (! $this->nomorForm) {
            $this->nomorForm = $this->generateNomorForm();
        }

        $data['nomor_form'] = $this->nomorForm;
        $form = FormPerawatan::create($data);
        $this->formId = $form->id;
        $this->isDraft = true;

        $this->syncItems($form);

        ActivityLogger::log('create', "Menyimpan draft form Perawatan: {$this->noAsset}", 'App\Models\FormPerawatan', $form->id);

        $this->dispatch('draftSaved');
        $this->redirect(route('forms.search'));
    }

    private function getFormData(): array
    {
        return [
            'user_id' => Auth::id(),
            'pengguna_employee_id' => $this->penggunaId,
            'asset_id' => $this->assetId,
            'site_location' => $this->siteLocation ?: null,
            'location_detail' => $this->locationDetail ?: null,
            'kondisi_akhir' => $this->kondisiAkhir ?: null,
            'kondisi_akhir_notes' => $this->kondisiAkhirNotes ?: null,
            'barcode_fisik' => $this->barcodeFisik,
            'notes' => $this->notes ?: null,
        ];
    }

    private function syncItems(FormPerawatan $form): void
    {
        $allItems = array_merge(
            array_map(fn ($i) => array_merge($i, ['category' => 'hardware']), $this->hardwareItems),
            array_map(fn ($i) => array_merge($i, ['category' => 'aplikasi']), $this->aplikasiItems),
            array_map(fn ($i) => array_merge($i, ['category' => 'operating_system']), $this->osItems),
        );

        foreach ($allItems as $item) {
            $form->items()->updateOrCreate(
                [
                    'template_item_id' => $item['template_item_id'],
                ],
                [
                    'category' => $item['category'],
                    'name' => $item['name'],
                    'status' => $item['status'] ?: null,
                    'keterangan' => $item['keterangan'] ?? null,
                    'sort_order' => $item['sort_order'] ?? 0,
                    'full_charge_capacity' => $item['full_charge_capacity'] ?? null,
                    'design_capacity' => $item['design_capacity'] ?? null,
                ]
            );
        }
    }

    public function submitForm(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->first();
            $this->dispatch('submitError', message: $firstError ?? 'Mohon lengkapi semua field yang wajib diisi');

            return;
        }

        DB::beginTransaction();

        try {
            $nomorForm = $this->formId
                ? FormPerawatan::find($this->formId)->nomor_form
                : $this->generateNomorForm();

            $data = $this->getFormData();
            $data['nomor_form'] = $nomorForm;
            $data['status'] = FormStatus::Submitted->value;
            $data['submitted_at'] = now();

            if ($this->formId) {
                $form = FormPerawatan::findOrFail($this->formId);
                $form->update($data);
            } else {
                $form = FormPerawatan::create($data);
                $this->formId = $form->id;
            }

            $this->syncItems($form);

            ActivityLogger::log('submit', "Mengirim form Perawatan: {$form->id} - {$this->noAsset}", 'App\Models\FormPerawatan', $form->id);

            if ($this->assetId && $this->penggunaId) {
                Asset::where('id', $this->assetId)->update([
                    'assigned_employee_id' => $this->penggunaId,
                    'status' => 'active',
                ]);
            }

            FormApproval::create([
                'approvable_type' => FormPerawatan::class,
                'approvable_id' => $form->id,
                'approval_level' => 'diperiksa_oleh',
                'user_id' => Auth::id(),
                'status' => 'pending',
            ]);

            DB::commit();

            $this->redirect(route('perawatan.signature', $form->id));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('submitError', message: $e->getMessage());
        }
    }

    public function getFormNumberPreview(): string
    {
        if ($this->noAsset) {
            return $this->generateNomorForm();
        }

        return '---/PWT/XXXX/'.now()->format('dmY');
    }

    public function render()
    {
        return view('livewire.perawatan.create-form')->layout('components.app-layout');
    }
}
