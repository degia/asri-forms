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
use Livewire\Component;

class EditForm extends Component
{
    public ?Employee $employee = null;
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
    public ?string $linkedUserId = null;

    public array $assignedAssets = [];

    public function mount(string $nik): void
    {
        $employee = Employee::with('user')->findOrFail($nik);
        $this->employee = $employee;
        $this->name = $employee->name ?? '';
        $this->nik = $employee->nik ?? '';
        $this->site = $employee->site ?? '';
        $this->directorate_id = $employee->directorate_id;
        $this->divisi_id = $employee->divisi_id;
        $this->departement_id = $employee->departement_id;
        $this->sub_departement_id = $employee->sub_departement_id;
        $this->position_id = $employee->position_id;
        $this->no_telepon = $employee->no_telepon ?? '';
        $this->email = $employee->user?->email ?? '';
        $this->status = $employee->status ?? Employee::STATUS_ACTIVE;
        $this->linkedUserId = $employee->user?->email;
        $this->assignedAssets = $employee->assignedAssets()
            ->get(['id', 'no_asset', 'nama_perangkat', 'brand', 'tipe', 'no_serial'])
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50|unique:employees,nik,' . $this->employee->nik . ',nik',
            'site' => 'nullable|string|max:50|exists:sites,id_site',
            'directorate_id' => 'nullable|integer|exists:directorates,id',
            'divisi_id' => 'nullable|integer|exists:divisis,id',
            'departement_id' => 'nullable|integer|exists:departements,id',
            'sub_departement_id' => 'nullable|integer|exists:sub_departements,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'no_telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $this->employee->nik . ',nik',
            'status' => 'nullable|in:Active,Resigned',
            'linkedUserId' => 'nullable|exists:users,email',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'nik.unique' => 'NIK sudah terdaftar pada employee lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar pada employee lain.',
            'status.in' => 'Status harus Active atau Resigned.',
            'linkedUserId.exists' => 'Akun login tidak valid.',
        ];
    }

    public function update(): void
    {
        try {
            $this->validate();

            if ($this->status === Employee::STATUS_RESIGNED && ! empty($this->assignedAssets)) {
                $this->addError(
                    'status',
                    'Employee masih memiliki ' . count($this->assignedAssets) . ' asset terpasang. Kembalikan asset terlebih dahulu melalui Form Pengembalian Asset.'
                );
                $this->dispatch('show-toast', message: 'Data gagal disimpan. Employee masih memiliki asset terpasang.', type: 'error');

                return;
            }

            if (! $this->structureIsConsistent()) {
                $this->dispatch('show-toast', message: 'Struktur organisasi tidak konsisten. Periksa kembali pilihan directorat/divisi/departemen/sub departemen.', type: 'error');

                return;
            }

            $this->renameNikIfChanged();

            $this->employee->update([
                'name' => $this->name,
                'nik' => $this->nik ?: null,
                'site' => $this->site ?: null,
                'directorate_id' => $this->directorate_id,
                'divisi_id' => $this->divisi_id,
                'departement_id' => $this->departement_id,
                'sub_departement_id' => $this->sub_departement_id,
                'position_id' => $this->position_id,
                'no_telepon' => $this->no_telepon ?: null,
                'status' => $this->status,
            ]);

            $this->syncLinkedUser();

            $this->employee->load('user');
            $linkedEmail = $this->employee->user?->email;
            $this->employee->update(['email' => $linkedEmail]);
            $this->email = $linkedEmail ?? '';

            $this->syncStatus();

            ActivityLogger::log('update', "Mengubah employee: {$this->name}", 'App\Models\Employee', $this->employee->nik);
            session()->flash('success', 'Employee berhasil diperbarui.');
            $this->redirect(route('admin.employees.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-toast', message: 'Data gagal disimpan. Periksa kembali isian form, termasuk NIK/Email yang sudah terdaftar.', type: 'error');
            $this->dispatch('validation-error', errors: $e->errors());
        }
    }

    private function renameNikIfChanged(): void
    {
        $oldNik = $this->employee->nik;
        $newNik = $this->nik ?: null;

        if ($newNik === $oldNik) {
            return;
        }

        if ($oldNik !== null) {
            User::where('nik', $oldNik)->update(['nik' => $newNik]);
        }
    }

    private function syncLinkedUser(): void
    {
        $currentUserEmail = $this->employee->user?->email;

        if ($currentUserEmail === $this->linkedUserId) {
            return;
        }

        if ($currentUserEmail) {
            $prev = User::find($currentUserEmail);
            if ($prev && $prev->nik === $this->employee->nik) {
                $prev->update(['nik' => null]);
            }
        }

        if ($this->linkedUserId) {
            $user = User::find($this->linkedUserId);
            if ($user) {
                $user->update(['nik' => $this->employee->nik]);
            }
        }

        $this->employee->load('user');
    }

    private function syncStatus(): void
    {
        $user = $this->employee->user;

        if ($this->status === Employee::STATUS_RESIGNED) {
            $this->employee->update([
                'akun_login' => 'No Access',
                'date_resign' => $this->employee->date_resign ?? today(),
            ]);

            if ($user) {
                $user->update(['status' => User::STATUS_RESIGNED]);
            }

            return;
        }

        $this->employee->update([
            'akun_login' => $this->employee->email ? 'Connect' : 'No Access',
            'date_resign' => null,
        ]);

        if ($user) {
            $user->update(['status' => User::STATUS_ACTIVE]);
        }
    }

    public function updatedLinkedUserId(?string $value): void
    {
        $this->email = $value ?? '';
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

    public function unlinkUser(): void
    {
        $this->linkedUserId = null;
        $this->email = '';
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

    public function getLinkableUsers(): array
    {
        return User::where(function ($q) {
            $q->whereNull('nik')
                ->orWhere('nik', $this->employee->nik);
        })
            ->orderBy('name')
            ->get(['email', 'name'])
            ->map(fn ($u) => [
                'id' => $u->email,
                'name' => $u->name,
                'email' => $u->email,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.employees.edit-form', [
            'linkableUsers' => $this->getLinkableUsers(),
        ]);
    }
}
