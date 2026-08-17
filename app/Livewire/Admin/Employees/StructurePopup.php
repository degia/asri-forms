<?php

namespace App\Livewire\Admin\Employees;

use App\Helpers\ActivityLogger;
use App\Models\Departement;
use App\Models\Directorate;
use App\Models\Divisi;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SubDepartement;
use Livewire\Attributes\On;
use Livewire\Component;

class StructurePopup extends Component
{
    public bool $open = false;

    public ?string $nik = null;

    public ?Employee $employee = null;

    public ?int $directorate_id = null;

    public ?int $divisi_id = null;

    public ?int $departement_id = null;

    public ?int $sub_departement_id = null;

    public ?int $position_id = null;

    public array $hierarchyTree = [];

    #[On('open-structure')]
    public function openPopup(string $nik): void
    {
        $this->nik = $nik;
        $this->employee = Employee::with([
            'directorate',
            'divisi.directorate',
            'departement.divisi.directorate',
            'subDepartement.departement.divisi.directorate',
            'position',
        ])->find($nik);

        if (! $this->employee) {
            return;
        }

        $this->directorate_id = $this->employee->directorate_id;
        $this->divisi_id = $this->employee->divisi_id;
        $this->departement_id = $this->employee->departement_id;
        $this->sub_departement_id = $this->employee->sub_departement_id;
        $this->position_id = $this->employee->position_id;

        $this->buildHierarchy();
        $this->open = true;
    }

    public function closePopup(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    public function updatedDirectorateId(): void
    {
        $this->divisi_id = null;
        $this->departement_id = null;
        $this->sub_departement_id = null;
        $this->rebuildHierarchy();
    }

    public function updatedDivisiId(): void
    {
        $this->departement_id = null;
        $this->sub_departement_id = null;
        $this->rebuildHierarchy();
    }

    public function updatedDepartementId(): void
    {
        $this->sub_departement_id = null;
        $this->rebuildHierarchy();
    }

    public function updatedSubDepartementId(): void
    {
        $this->rebuildHierarchy();
    }

    public function updatedPositionId(): void
    {
        $this->rebuildHierarchy();
    }

    public function save(): void
    {
        if (! $this->employee) {
            return;
        }

        $this->employee->update([
            'directorate_id' => $this->directorate_id,
            'divisi_id' => $this->divisi_id,
            'departement_id' => $this->departement_id,
            'sub_departement_id' => $this->sub_departement_id,
            'position_id' => $this->position_id,
        ]);

        $this->employee->load([
            'directorate',
            'divisi.directorate',
            'departement.divisi.directorate',
            'subDepartement.departement.divisi.directorate',
            'position',
        ]);

        ActivityLogger::log(
            'update',
            "Mengubah struktur employee: {$this->employee->name}",
            'App\Models\Employee',
            $this->employee->nik
        );

        $this->dispatch('show-toast', message: 'Struktur organisasi berhasil diperbarui.', type: 'success');
        $this->dispatch('structure-updated', nik: $this->employee->nik);
        $this->closePopup();
    }

    private function rebuildHierarchy(): void
    {
        $this->buildHierarchy();
    }

    private function buildHierarchy(): void
    {
        $directorate = $this->directorate_id
            ? Directorate::with(['divisis.departements.subDepartements'])->find($this->directorate_id)
            : null;

        $highlightName = collect([
            $directorate?->name,
            $this->divisi_id ? Divisi::find($this->divisi_id)?->name : null,
            $this->departement_id ? Departement::find($this->departement_id)?->name : null,
            $this->sub_departement_id ? SubDepartement::find($this->sub_departement_id)?->name : null,
        ])->filter()->values()->all();

        $this->hierarchyTree = $directorate
            ? [$this->buildNode($directorate, $highlightName)]
            : [];
    }

    private function buildNode($node, array $highlightNames): array
    {
        $className = get_class($node);
        $typeLabel = match ($className) {
            Directorate::class => 'Directorat',
            Divisi::class => 'Divisi',
            Departement::class => 'Departemen',
            SubDepartement::class => 'Sub Departemen',
            default => '',
        };

        $result = [
            'id' => $node->getKey(),
            'name' => $node->name,
            'type' => $typeLabel,
            'highlight' => in_array($node->name, $highlightNames),
            'children' => [],
        ];

        $children = match ($className) {
            Directorate::class => $node->divisis,
            Divisi::class => $node->departements,
            Departement::class => $node->subDepartements,
            default => collect(),
        };

        foreach ($children as $child) {
            $result['children'][] = $this->buildNode($child, $highlightNames);
        }

        return $result;
    }

    public function getDirectorateList(): array
    {
        return Directorate::orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getDivisiList(): array
    {
        if (! $this->directorate_id) {
            return [];
        }

        return Divisi::where('directorate_id', $this->directorate_id)
            ->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getDepartementList(): array
    {
        if (! $this->divisi_id) {
            return [];
        }

        return Departement::where('divisi_id', $this->divisi_id)
            ->orderBy('name')->get(['id', 'name'])
            ->mapWithKeys(fn ($d) => [$d->id => $d->name])
            ->toArray();
    }

    public function getSubDepartementList(): array
    {
        if (! $this->departement_id) {
            return [];
        }

        return SubDepartement::where('departement_id', $this->departement_id)
            ->orderBy('name')->get(['id', 'name'])
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

    public function render()
    {
        return view('livewire.admin.employees.structure-popup');
    }
}
