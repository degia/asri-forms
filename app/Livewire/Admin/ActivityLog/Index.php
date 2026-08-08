<?php

namespace App\Livewire\Admin\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterUserId = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterUserId' => ['except' => ''],
    ];

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterUserId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterUserId = '';
        $this->resetPage();
    }

    public function clearAll(): void
    {
        ActivityLog::truncate();
        session()->flash('success', 'Semua log aktivitas berhasil dihapus.');
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLog::with('user');

        if ($this->search) {
            $query->where('description', 'like', '%' . $this->search . '%');
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $logs = $query->paginate(20);

        $types = ActivityLog::select('type')->distinct()->orderBy('type')->pluck('type');

        $users = User::whereHas('activityLogs')->orderBy('name')->get(['email', 'name']);

        return view('livewire.admin.activity-log.index', [
            'logs' => $logs,
            'types' => $types,
            'users' => $users,
        ]);
    }
}
