<?php

namespace App\Livewire\Users;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterName = '';
    public string $filterEmail = '';
    public string $filterNik = '';
    public string $filterSite = '';
    public string $filterRole = '';
    public string $filterStatusEmployee = '';
    public string $filterAccessLogin = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public bool $showDeleteModal = false;
    public ?string $deleteUserId = null;
    public string $deleteUserName = '';
    public array $selected = [];
    public bool $showBulkDeleteModal = false;
    public bool $showBulkEditModal = false;
    public string $bulkEditField = '';
    public string $bulkEditValue = '';

    protected $queryString = [
        'filterName' => ['except' => ''],
        'filterEmail' => ['except' => ''],
        'filterNik' => ['except' => ''],
        'filterSite' => ['except' => ''],
        'filterRole' => ['except' => ''],
        'filterStatusEmployee' => ['except' => ''],
        'filterAccessLogin' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'filter')) {
            $this->resetPage();
        }
    }

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function getRoleList(): array
    {
        return \Spatie\Permission\Models\Role::pluck('name')->toArray();
    }

    public function getRoleBadge(string $role): string
    {
        return match ($role) {
            'admin' => 'bg-red-500/15 text-red-400',
            'teknisi' => 'bg-blue-500/15 text-blue-400',
            'supervisor_it' => 'bg-yellow-500/15 text-yellow-400',
            'manager_it' => 'bg-purple-500/15 text-purple-400',
            'pengguna' => 'bg-emerald-500/15 text-emerald-400',
            default => 'bg-gray-500/15 text-gray-400',
        };
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

    public function getEmployeeStatusBadge(?string $status): string
    {
        return $status === \App\Models\Employee::STATUS_RESIGNED
            ? 'bg-gray-500/15 text-gray-400'
            : 'bg-emerald-500/15 text-emerald-400';
    }

    public function getEmployeeStatusLabel(?string $status): string
    {
        return $status === \App\Models\Employee::STATUS_RESIGNED ? 'Resigned' : ($status ? 'Active' : '-');
    }

    public function getAccessLoginBadge(string $status): string
    {
        return $status === User::STATUS_RESIGNED
            ? 'bg-gray-500/15 text-gray-400'
            : 'bg-emerald-500/15 text-emerald-400';
    }

    public function getAccessLoginLabel(string $status): string
    {
        return $status === User::STATUS_RESIGNED ? 'Disable' : 'Enable';
    }

    public function confirmDelete(string $email, string $name): void
    {
        $this->deleteUserId = $email;
        $this->deleteUserName = $name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteUserId = null;
        $this->deleteUserName = '';
    }

    public function deleteUser(): void
    {
        if ($this->deleteUserId === Auth::id()) {
            $this->dispatch('delete-error', message: 'Tidak bisa menghapus akun sendiri.');
            $this->cancelDelete();
            return;
        }

        User::findOrFail($this->deleteUserId)->delete();
        $this->selected = array_values(array_diff($this->selected, [$this->deleteUserId]));

        ActivityLogger::log('delete', "Menghapus user: {$this->deleteUserName}", 'App\Models\User', $this->deleteUserId);
        $this->cancelDelete();
        $this->dispatch('user-deleted');
    }

    public function toggleSelectAll(): void
    {
        $pageIds = collect($this->orderedQuery()
            ->paginate(12)->items())->pluck('email')->all();

        if (count($pageIds) === count(array_intersect($pageIds, $this->selected))) {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        }
    }

    public function confirmBulkDelete(): void
    {
        $this->showBulkDeleteModal = true;
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function bulkDelete(): void
    {
        $users = User::whereIn('email', $this->selected)->get();
        $deleted = 0;
        foreach ($users as $user) {
            if ($user->email === Auth::id()) continue;
            $user->delete();
            $deleted++;
        }

        ActivityLogger::log('delete', "Menghapus {$deleted} user secara massal");
        $this->selected = [];
        $this->cancelBulkDelete();
        $this->dispatch('show-toast', message: "{$deleted} user berhasil dihapus.", type: 'success');
        $this->dispatch('user-deleted');
    }

    public function openBulkEdit(): void
    {
        $this->bulkEditField = '';
        $this->bulkEditValue = '';
        $this->showBulkEditModal = true;
    }

    public function cancelBulkEdit(): void
    {
        $this->showBulkEditModal = false;
        $this->bulkEditField = '';
        $this->bulkEditValue = '';
    }

    public function bulkEdit(): void
    {
        if (empty($this->selected)) {
            $this->cancelBulkEdit();
            return;
        }

        $allowed = ['role', 'access_login', 'name', 'email', 'nik'];
        if (!in_array($this->bulkEditField, $allowed)) {
            $this->addError('bulkEditField', 'Pilih field terlebih dahulu.');
            return;
        }

        if ($this->bulkEditField === 'role') {
            if (!$this->bulkEditValue) {
                $this->addError('bulkEditValue', 'Pilih role terlebih dahulu.');
                return;
            }

            $count = 0;
            foreach (User::whereIn('email', $this->selected)->get() as $user) {
                $user->syncRoles([$this->bulkEditValue]);
                $count++;
            }

            ActivityLogger::log('update', "Mengubah role {$count} user menjadi {$this->bulkEditValue}");
            $this->dispatch('show-toast', message: "Role {$count} user diperbarui menjadi {$this->getRoleLabel($this->bulkEditValue)}.", type: 'success');
        } elseif ($this->bulkEditField === 'access_login') {
            if (!in_array($this->bulkEditValue, [User::STATUS_ACTIVE, User::STATUS_RESIGNED], true)) {
                $this->addError('bulkEditValue', 'Pilih akses login terlebih dahulu.');
                return;
            }

            $count = User::whereIn('email', $this->selected)->update(['status' => $this->bulkEditValue]);

            ActivityLogger::log('update', "Mengubah akses login {$count} user menjadi " . $this->getAccessLoginLabel($this->bulkEditValue));
            $this->dispatch('show-toast', message: "Access Login {$count} user diperbarui menjadi {$this->getAccessLoginLabel($this->bulkEditValue)}.", type: 'success');
        } else {
            $value = trim($this->bulkEditValue);
            $count = User::whereIn('email', $this->selected)->update([$this->bulkEditField => $value ?: null]);

            ActivityLogger::log('update', "Mengubah {$this->bulkEditField} {$count} user menjadi '{$value}'");
            $this->dispatch('show-toast', message: "{$this->getBulkEditFieldLabel($this->bulkEditField)} {$count} user diperbarui.", type: 'success');
        }

        $this->selected = [];
        $this->cancelBulkEdit();
        $this->dispatch('user-updated');
    }

    public function getBulkEditFieldLabel(string $field): string
    {
        return match ($field) {
            'role' => 'Role',
            'access_login' => 'Access Login',
            'name' => 'Nama',
            'email' => 'Email',
            'nik' => 'NIK',
            default => ucfirst($field),
        };
    }

    private function filteredQuery()
    {
        return User::query()
            ->with(['roles', 'employee'])
            ->leftJoin('employees', 'employees.nik', '=', 'users.nik')
            ->select('users.*')
            ->when($this->filterName, fn ($q) => $q->where('users.name', 'like', "%{$this->filterName}%"))
            ->when($this->filterEmail, fn ($q) => $q->where('users.email', 'like', "%{$this->filterEmail}%"))
            ->when($this->filterNik, fn ($q) => $q->where('users.nik', 'like', "%{$this->filterNik}%"))
            ->when($this->filterSite, fn ($q) => $q->whereHas('employee', fn ($q) => $q->where('site', 'like', "%{$this->filterSite}%")))
            ->when($this->filterRole, fn ($q) => $q->role($this->filterRole))
            ->when($this->filterStatusEmployee, fn ($q) => $q->whereHas('employee', fn ($q) => $q->where('status', $this->filterStatusEmployee)))
            ->when($this->filterAccessLogin, fn ($q) => $q->where('users.status', $this->filterAccessLogin));
    }

    private function orderedQuery()
    {
        $query = $this->filteredQuery();

        if ($this->sortBy === 'name') {
            $query->orderByRaw('COALESCE(employees.name, users.name) ' . $this->sortDirection)
                ->orderBy('users.name', $this->sortDirection);
        } else {
            $query->orderBy('users.' . $this->sortBy, $this->sortDirection);
        }

        return $query;
    }

    public function render()
    {
        $users = $this->orderedQuery()->paginate(12);

        $pageIds = collect($users->items())->pluck('email')->all();
        $allSelected = count($pageIds) > 0 && count(array_intersect($this->selected, $pageIds)) === count($pageIds);

        return view('livewire.users.index', [
            'users' => $users,
            'pageIds' => $pageIds,
            'allSelected' => $allSelected,
        ]);
    }
}
