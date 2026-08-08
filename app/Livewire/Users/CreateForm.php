<?php

namespace App\Livewire\Users;

use App\Helpers\ActivityLogger;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = 'password';
    public string $password_confirmation = 'password';
    public string $nik = '';
    public string $status = User::STATUS_ACTIVE;
    public string $role = 'pengguna';

    public bool $showCredentials = false;
    public string $createdEmail = '';
    public string $createdPassword = '';

    public bool $showAddEmployeeModal = false;

    public function getLinkedEmployeeProperty(): ?Employee
    {
        $nik = trim($this->nik);

        return $nik !== '' ? Employee::where('nik', $nik)->first() : null;
    }

    public function updatedNik(string $value): void
    {
        $this->name = Employee::where('nik', trim($value))->value('name') ?? '';
    }

    public function openAddEmployeeModal(): void
    {
        $this->showAddEmployeeModal = true;
    }

    public function closeAddEmployeeModal(): void
    {
        $this->showAddEmployeeModal = false;
        $this->resetErrorBag('nik');
    }

    #[On('employee-created')]
    public function onEmployeeCreated(string $nik): void
    {
        $this->showAddEmployeeModal = false;
        $this->resetErrorBag('nik');
        $this->nik = $nik;
        $this->updatedNik($this->nik);
        $this->dispatch('show-toast', message: 'Employee berhasil ditambahkan.', type: 'success');
    }

    #[On('close-employee-modal')]
    public function closeEmployeeModal(): void
    {
        $this->closeAddEmployeeModal();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'nik' => 'required|string|max:50|unique:users,nik',
            'status' => 'nullable|in:Enable,Disable',
            'role' => 'required|exists:roles,name',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'status.in' => 'Access Login harus Enable atau Disable.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role tidak valid.',
        ];
    }

    public function save(): void
    {
        if ($this->nik === '') {
            $this->addError('nik', 'NIK wajib diisi.');
            $this->dispatch('show-toast', message: 'NIK wajib diisi.', type: 'error');

            return;
        }

        if (! $this->linkedEmployee) {
            $this->addError('nik', 'NIK belum terdaftar pada data employee. Tambahkan employee terlebih dahulu.');
            $this->dispatch('show-toast', message: 'NIK belum terdaftar pada data employee.', type: 'error');

            return;
        }

        $this->name = $this->linkedEmployee->name;

        try {
            $this->validate();

            $plainPassword = $this->password;

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($plainPassword),
                'nik' => $this->nik ?: null,
                'status' => $this->status,
            ]);

            $user->syncEmployeeLink();

            if ($user->employee
                && ! Employee::where('email', $user->email)
                    ->where('nik', '!=', $user->nik)
                    ->exists()) {
                $user->employee->update(['email' => $user->email]);
            }

            $user->assignRole($this->role);

            ActivityLogger::log('create', "Menambahkan user baru: {$this->name} ({$this->email})", 'App\Models\User', $user->email);
            $this->createdEmail = $this->email;
            $this->createdPassword = $plainPassword;
            $this->showCredentials = true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-error', errors: $e->errors());
        }
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

    public function render()
    {
        return view('livewire.users.create-form');
    }
}
