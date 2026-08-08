<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use Livewire\Component;

class Detail extends Component
{
    public ?Asset $asset = null;
    public array $timeline = [];

    public function mount(string $id): void
    {
        $this->asset = Asset::with(['pemeriksaan.teknisi', 'pemeriksaan.pengguna', 'pemeriksaan.approvals', 'perawatan.teknisi', 'perawatan.pengguna', 'perawatan.approvals'])
            ->findOrFail($id);

        $this->buildTimeline();
    }

    private function buildTimeline(): void
    {
        $events = [];

        foreach ($this->asset->pemeriksaan as $form) {
            $events[] = [
                'type' => 'pemeriksaan',
                'id' => $form->id,
                'nomor_form' => $form->nomor_form,
                'status' => $form->status,
                'teknisi' => $form->teknisi->name,
                'pengguna' => $form->pengguna->name ?? '-',
                'date' => $form->submitted_at,
                'created_at' => $form->created_at,
                'kondisi' => $form->kondisi,
            ];
        }

        foreach ($this->asset->perawatan as $form) {
            $events[] = [
                'type' => 'perawatan',
                'id' => $form->id,
                'nomor_form' => $form->nomor_form,
                'status' => $form->status,
                'teknisi' => $form->teknisi->name,
                'pengguna' => $form->pengguna->name ?? '-',
                'date' => $form->submitted_at,
                'created_at' => $form->created_at,
                'kondisi' => $form->kondisi_akhir,
            ];
        }

        usort($events, fn($a, $b) => ($b['date'] ?? $b['created_at']) <=> ($a['date'] ?? $a['created_at']));

        $this->timeline = $events;
    }

    public function getKondisiLabel(?string $kondisi): string
    {
        return match ($kondisi) {
            'baru' => 'Baru',
            'lama' => 'Lama',
            'good' => 'Good',
            'fair' => 'Fair',
            'critical' => 'Critical',
            'poor' => 'Poor',
            default => '-',
        };
    }

    public function getKondisiColor(?string $kondisi): string
    {
        return match ($kondisi) {
            'baru', 'good' => 'text-emerald-400',
            'fair' => 'text-blue-400',
            'critical' => 'text-red-400',
            'lama', 'poor' => 'text-amber-400',
            default => 'text-secondary',
        };
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'draft' => 'bg-gray-500/15 text-gray-400',
            'submitted' => 'bg-blue-500/15 text-blue-400',
            'diketahui' => 'bg-yellow-500/15 text-yellow-400',
            'disetujui' => 'bg-green-500/15 text-green-400',
            'selesai' => 'bg-emerald-500/15 text-emerald-400',
            'revisi' => 'bg-red-500/15 text-red-400',
            default => 'bg-gray-500/15 text-gray-400',
        };
    }

    public function render()
    {
        return view('livewire.assets.detail')->layout('components.app-layout');
    }
}
