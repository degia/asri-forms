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
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CreateForm extends Component
{
    public string $name = '';
    public string $nik = '';
    public string $site = '';
    public ?int $directorate_id = null;
    public ?int $divisi_id = null;
    public ?int $departement_id = null;
    public ?int $sub_departement_id = null;
    public ?int $position_id = null;
    public string $no_telepon = '';
    public string $email = '';
    public string $status = Employee::STATUS_ACTIVE;

    public bool $modal = false;

    public array $emailSuggestions = [];

    public bool $showCreateUserModal = false;
    public string $newUserName = '';
    public string $newUserEmail = '';
    public string $newUserPassword = 'password';
    public string $newUserRole = 'pengguna';

    public function mount(?string $nik = null, ?string $name = null, bool $modal = false): void
    {
        $this->modal = $modal;

        if ($nik !== null) {
            $this->nik = trim($nik);
        } elseif (is_string(request()->query('nik')) && trim(request()->query('nik')) !== '') {
            $this->nik = trim(request()->query('nik'));
        }

        if ($name !== null) {
            $this->name = trim($name);
        } elseif (is_string(request()->query('name')) && trim(request()->query('name')) !== '') {
            $this->name = trim(request()->query('name'));
        }
    }

    public function closeModal(): void
    {
        $this->dispatch('close-employee-modal');
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50|unique:employees,nik',
            'site' => 'nullable|string|max:50|exists:sites,id_site',
            'directorate_id' => 'nullable|integer|exists:directorates,id',
            'divisi_id' => 'nullable|integer|exists:divisis,id',
            'departement_id' => 'nullable|integer|exists:departements,id',
            'sub_departement_id' => 'nullable|integer|exists:sub_departements,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'no_telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:employees,email|exists:users,email',
            'status' => 'nullable|in:Active,Resigned',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'nik.unique' => 'NIK sudah terdaftar pada employee lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh employee lain.',
            'email.exists' => 'Email harus terdaftar sebagai akun user terlebih dahulu.',
            'status.in' => 'Status harus Active atau Resigned.',
        ];
    }

    public function updatedEmail(string $value): void
    {
        $this->emailSuggestions = [];

        if (trim($value) === '') {
            return;
        }

        $this->emailSuggestions = User::where('email', 'like', '%'.$value.'%')
            ->limit(8)
            ->get(['email', 'name'])
            ->map(fn ($u) => ['email' => $u->email, 'name' => $u->name])
            ->toArray();
    }

    public function updatedDirectorateId(): void
    {
        $this->divisi_id = null;
        $this->departement_id = null;
        $this->sub_departement_id = null;
    }

    public function updatedDivisiId(): void
    {
        $this->departement_id = null;
        $this->sub_departement_id = null;
    }

    public function updatedDepartementId(): void
    {
        $this->sub_departement_id = null;
    }

    public function getEmailRegisteredProperty(): bool
    {
        return $this->email !== '' && User::where('email', $this->email)->withTrashed()->exists();
    }

    public function getEmailUsedProperty(): bool
    {
        return $this->email !== '' && Employee::where('email', $this->email)->exists();
    }

    public function selectEmail(string $email): void
    {
        $this->email = $email;
        $this->emailSuggestions = [];
        $this->resetValidation('email');
    }

    public function openCreateUserModal(): void
    {
        $this->newUserName = $this->name;
        $this->newUserEmail = $this->email;
        $this->newUserPassword = 'password';
        $this->newUserRole = 'pengguna';
        $this->showCreateUserModal = true;
        $this->dispatch('open-modal', 'create-user');
        $this->resetValidation();
    }

    public function closeCreateUserModal(): void
    {
        $this->showCreateUserModal = false;
        $this->dispatch('close-modal', 'create-user');
        $this->resetValidation();
    }

    public function createUser(): void
    {
        $this->validate([
            'newUserName' => 'required|string|max:255',
            'newUserEmail' => 'required|email|unique:users,email',
            'newUserPassword' => 'required|string|min:6',
            'newUserRole' => 'required|exists:roles,name',
        ], [
            'newUserName.required' => 'Nama wajib diisi.',
            'newUserEmail.required' => 'Email wajib diisi.',
            'newUserEmail.email' => 'Format email tidak valid.',
            'newUserEmail.unique' => 'Email sudah terdaftar.',
            'newUserPassword.required' => 'Password wajib diisi.',
            'newUserPassword.min' => 'Password minimal 6 karakter.',
            'newUserRole.required' => 'Role wajib dipilih.',
            'newUserRole.exists' => 'Role tidak valid.',
        ]);

        $user = User::create([
            'name' => $this->newUserName,
            'email' => $this->newUserEmail,
            'password' => Hash::make($this->newUserPassword),
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->assignRole($this->newUserRole);

        ActivityLogger::log('create', "Menambahkan user baru: {$this->newUserName} ({$this->newUserEmail})", 'App\Models\User', $user->email);

        $this->email = $this->newUserEmail;
        $this->emailSuggestions = [];
        $this->showCreateUserModal = false;
        $this->dispatch('close-modal', 'create-user');
        $this->dispatch('show-toast', message: 'Akun user berhasil dibuat. Email dapat dipakai pada employee ini.', type: 'success');
    }

    public function getRoleList(): array
    {
        return \Spatie\Permission\Models\Role::pluck('name')->toArray();
    }

    public function getRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'teknisi' => 'Teknisi',
            'supervisor_it' => 'Supervisor IT',
            'manager_it' => 'Manager IT',
            'pengguna' => 'Pengguna',
            default => ucfirst($role),
        };
    }

    public function save(): void
    {
        if ($this->email !== '' && $this->emailUsed) {
            $this->addError('email', 'Email sudah digunakan oleh employee lain.');
            $this->dispatch('show-toast', message: 'Email sudah digunakan oleh employee lain. Data tidak dapat disimpan.', type: 'error');

            return;
        }

        if (! $this->structureIsConsistent()) {
            $this->dispatch('show-toast', message: 'Struktur organisasi tidak konsisten. Periksa kembali pilihan directorat/divisi/departemen/sub departemen.', type: 'error');

            return;
        }

        try {
            $this->validate();

            $employee = Employee::create([
                'name' => $this->name,
                'nik' => $this->nik ?: null,
                'site' => $this->site ?: null,
                'directorate_id' => $this->directorate_id,
                'divisi_id' => $this->divisi_id,
                'departement_id' => $this->departement_id,
                'sub_departement_id' => $this->sub_departement_id,
                'position_id' => $this->position_id,
                'no_telepon' => $this->no_telepon ?: null,
                'email' => $this->email ?: null,
                'status' => $this->status,
                'akun_login' => $this->email ? 'Connect' : 'No Access',
            ]);

            if ($this->email) {
                $user = User::find($this->email);
                if ($user) {
                    if ($user->status === User::STATUS_RESIGNED) {
                        $employee->update(['akun_login' => 'No Access']);
                    }
                    $user->update(['nik' => $employee->nik]);
                }
            }

            ActivityLogger::log('create', "Menambahkan employee baru: {$this->name}", 'App\Models\Employee', $employee->nik);

            if ($this->modal) {
                $this->dispatch('employee-created', nik: $employee->nik, name: $employee->name);

                return;
            }

            session()->flash('success', 'Employee berhasil ditambahkan.');
            $this->redirect(route('admin.employees.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toast', message: 'Data gagal disimpan. Periksa kembali isian form, termasuk NIK/Email yang sudah terdaftar.', type: 'error');
            $this->dispatch('validation-error', errors: $e->errors());
        }
    }

    public function getSiteList(): array
    {
        return Site::orderBy('id_site')->get(['id_site', 'site'])
            ->mapWithKeys(fn ($s) => [$s->id_site => "{$s->id_site} - {$s->site}"])
            ->toArray();
    }

    public function getDirectorateList(): array
    {
        return Directorate::orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getDivisiList(): array
    {
        return Divisi::where('directorate_id', $this->directorate_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getDepartementList(): array
    {
        return Departement::where('divisi_id', $this->divisi_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getSubDepartementList(): array
    {
        return SubDepartement::where('departement_id', $this->departement_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getPositionList(): array
    {
        return Position::orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($p) => [$p->id => $p->name])
            ->toArray();
    }

    private function structureIsConsistent(): bool
    {
        if ($this->divisi_id && $this->directorate_id
            && ! Divisi::where('id', $this->divisi_id)->where('directorate_id', $this->directorate_id)->exists()) {
            $this->addError('divisi_id', 'Divisi harus berasal dari Directorat terpilih.');

            return false;
        }

        if ($this->departement_id && $this->divisi_id
            && ! Departement::where('id', $this->departement_id)->where('divisi_id', $this->divisi_id)->exists()) {
            $this->addError('departement_id', 'Departemen harus berasal dari Divisi terpilih.');

            return false;
        }

        if ($this->sub_departement_id && $this->departement_id
            && ! SubDepartement::where('id', $this->sub_departement_id)->where('departement_id', $this->departement_id)->exists()) {
            $this->addError('sub_departement_id', 'Sub Departemen harus berasal dari Departemen terpilih.');

            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.admin.employees.create-form');
    }
}
