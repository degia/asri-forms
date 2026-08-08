<?php

namespace App\Livewire\Layout;

use App\Enums\ApprovalLevel;
use App\Models\FormPemeriksaan;
use App\Models\FormPerawatan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $pendingCount = 0;
    public array $notifications = [];
    public bool $showDropdown = false;

    protected $listeners = [
        'notification-updated' => 'loadNotifications',
        'approveForm' => 'loadNotifications',
        'rejectForm' => 'loadNotifications',
    ];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = Auth::user();
        $this->notifications = [];
        $this->pendingCount = 0;

        $this->loadPemeriksaanNotifications($user);
        $this->loadPerawatanNotifications($user);
    }

    private function loadPemeriksaanNotifications($user): void
    {
        $queryDiketahui = FormPemeriksaan::where('status', 'diketahui')
            ->with(['teknisi', 'asset'])
            ->where('pengguna_employee_id', $user->nik);

        foreach ($queryDiketahui->get() as $form) {
            $this->notifications[] = [
                'type' => 'pemeriksaan',
                'id' => $form->id,
                'nomor_form' => $form->nomor_form,
                'submitted_by' => $form->teknisi->name,
                'device_name' => $form->asset->nama_perangkat,
                'level' => 'Diketahui Oleh',
                'level_key' => 'diketahui_oleh',
                'created_at' => $form->submitted_at?->diffForHumans() ?? '-',
            ];
            $this->pendingCount++;
        }

        if ($user->hasAnyRole(['supervisor_it', 'manager_it', 'admin'])) {
            $forms = FormPemeriksaan::where('status', 'disetujui')
                ->with(['teknisi', 'asset'])
                ->get();

            foreach ($forms as $form) {
                $this->notifications[] = [
                    'type' => 'pemeriksaan',
                    'id' => $form->id,
                    'nomor_form' => $form->nomor_form,
                    'submitted_by' => $form->teknisi->name,
                    'device_name' => $form->asset->nama_perangkat,
                    'level' => 'Disetujui Oleh',
                    'level_key' => 'disetujui_oleh',
                    'created_at' => $form->submitted_at?->diffForHumans() ?? '-',
                ];
                $this->pendingCount++;
            }
        }
    }

    private function loadPerawatanNotifications($user): void
    {
        $queryDiketahui = FormPerawatan::where('status', 'diketahui')
            ->with(['teknisi', 'asset'])
            ->where('pengguna_employee_id', $user->nik);

        foreach ($queryDiketahui->get() as $form) {
            $this->notifications[] = [
                'type' => 'perawatan',
                'id' => $form->id,
                'nomor_form' => $form->nomor_form,
                'submitted_by' => $form->teknisi->name,
                'device_name' => $form->asset->nama_perangkat,
                'level' => 'Diketahui Oleh',
                'level_key' => 'diketahui_oleh',
                'created_at' => $form->submitted_at?->diffForHumans() ?? '-',
            ];
            $this->pendingCount++;
        }

        if ($user->hasAnyRole(['supervisor_it', 'manager_it', 'admin'])) {
            $forms = FormPerawatan::where('status', 'disetujui')
                ->with(['teknisi', 'asset'])
                ->get();

            foreach ($forms as $form) {
                $this->notifications[] = [
                    'type' => 'perawatan',
                    'id' => $form->id,
                    'nomor_form' => $form->nomor_form,
                    'submitted_by' => $form->teknisi->name,
                    'device_name' => $form->asset->nama_perangkat,
                    'level' => 'Disetujui Oleh',
                    'level_key' => 'disetujui_oleh',
                    'created_at' => $form->submitted_at?->diffForHumans() ?? '-',
                ];
                $this->pendingCount++;
            }
        }
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function render()
    {
        return view('livewire.layout.notification-bell');
    }
}
