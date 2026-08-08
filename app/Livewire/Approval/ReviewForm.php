<?php

namespace App\Livewire\Approval;

use App\Helpers\ActivityLogger;
use App\Enums\ApprovalLevel;
use App\Enums\FormStatus;
use App\Models\FormApproval;
use App\Models\Employee;
use App\Models\FormPemeriksaan;
use App\Models\FormPemeriksaanItem;
use App\Models\FormPerawatan;
use App\Models\FormPerawatanItem;
use App\Models\User;
use App\Notifications\ApprovalRequestNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReviewForm extends Component
{
    public ?FormPemeriksaan $pemeriksaanForm = null;

    public ?FormPerawatan $perawatanForm = null;

    public string $formType = '';

    public ?int $formId = null;

    public ?FormApproval $currentApproval = null;

    public string $approvalLevel = '';

    public bool $canApprove = false;

    public bool $canEditAsTeknisi = false;

    public string $catatan = '';

    public bool $saved = false;

    public bool $rejected = false;

    public string $rejectReason = '';

    public bool $showRejectModal = false;

    // Signer mode for Diketahui
    public string $signerMode = 'me';

    public string $customSignerName = '';

    public array $signerResults = [];

    public bool $showSignerDropdown = false;

    // Edit mode
    public bool $editing = false;

    public string $editNotes = '';

    public string $editKondisi = '';

    public string $editKondisiKeterangan = '';

    public array $editItems = [];

    // User saved signature
    public ?string $userSignature = null;

    public function mount(string $type, string $id): void
    {
        $this->formType = $type;
        $this->formId = (int) $id;
        $user = Auth::user();

        if ($type === 'pemeriksaan') {
            $this->pemeriksaanForm = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals'])
                ->findOrFail($this->formId);
        } elseif ($type === 'perawatan') {
            $this->perawatanForm = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'site', 'items', 'approvals'])
                ->findOrFail($this->formId);
        } else {
            abort(404);
        }

        $form = $this->getForm();
        $currentStatus = $this->getForm()->status;

        if ($currentStatus === FormStatus::Draft->value) {
            abort(403, 'Form tidak tersedia untuk approval.');
        }

        $this->determineApprovalLevel($user, $form);

        $this->userSignature = $user->signature_path;
    }

    private function getForm(): FormPemeriksaan|FormPerawatan
    {
        return $this->formType === 'pemeriksaan' ? $this->pemeriksaanForm : $this->perawatanForm;
    }

    private function determineApprovalLevel($user, $form): void
    {
        $pendingStatuses = [FormStatus::Diketahui->value, FormStatus::Submitted->value, FormStatus::Revisi->value];

        if ($this->formType === 'pemeriksaan') {
            if ($user->nik && $form->pengguna_employee_id === $user->nik && in_array($form->status, $pendingStatuses)) {
                $this->approvalLevel = ApprovalLevel::DiketahuiOleh->value;
                $this->canApprove = true;
            } elseif ($user->hasAnyRole(['supervisor_it', 'manager_it', 'admin']) && $form->status === FormStatus::Disetujui->value) {
                $this->approvalLevel = ApprovalLevel::DisetujuiOleh->value;
                $this->canApprove = true;
            }
        } else {
            if ($user->nik && $form->pengguna_employee_id === $user->nik && in_array($form->status, $pendingStatuses)) {
                $this->approvalLevel = ApprovalLevel::DiketahuiOleh->value;
                $this->canApprove = true;
            } elseif ($user->hasAnyRole(['supervisor_it', 'manager_it', 'admin']) && $form->status === FormStatus::Disetujui->value) {
                $this->approvalLevel = ApprovalLevel::DisetujuiOleh->value;
                $this->canApprove = true;
            }
        }

        // Teknisi (creator) bisa edit selama belum di-approve oleh Disetujui
        if (! $this->canApprove
            && $form->user_id === $user->email
            && in_array($form->status, [FormStatus::Submitted->value, FormStatus::Diketahui->value])) {
            $this->canEditAsTeknisi = true;
        }

        $this->currentApproval = $form->approvals()
            ->where('approval_level', $this->approvalLevel)
            ->first();
    }

    // ─── Edit Mode ────────────────────────────────────────

    public function toggleEdit(): void
    {
        if (! $this->editing) {
            $this->loadEditData();
        }
        $this->editing = ! $this->editing;
    }

    private function loadEditData(): void
    {
        $form = $this->getForm();

        $this->editNotes = $form->notes ?? '';
        $this->editKondisi = $form->kondisi ?? $form->kondisi_akhir ?? '';
        $this->editKondisiKeterangan = $form->kondisi_keterangan ?? $form->kondisi_akhir_notes ?? '';

        $this->editItems = $form->items->sortBy('sort_order')->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'status' => $item->status ?? '',
                'value' => $item->value ?? '',
                'keterangan' => $item->keterangan ?? '',
            ];
        })->toArray();
    }

    public function saveEdits(): void
    {
        $form = $this->getForm();

        DB::beginTransaction();

        try {
            // Update form-level fields
            if ($this->formType === 'pemeriksaan') {
                $form->update([
                    'notes' => $this->editNotes ?: null,
                    'kondisi' => $this->editKondisi ?: null,
                    'kondisi_keterangan' => $this->editKondisiKeterangan ?: null,
                ]);
            } else {
                $form->update([
                    'notes' => $this->editNotes ?: null,
                    'kondisi_akhir' => $this->editKondisi ?: null,
                    'kondisi_akhir_notes' => $this->editKondisiKeterangan ?: null,
                ]);
            }

            // Update item fields
            foreach ($this->editItems as $editItem) {
                if ($this->formType === 'pemeriksaan') {
                    FormPemeriksaanItem::where('id', $editItem['id'])->update([
                        'status' => $editItem['status'] ?: null,
                        'value' => $editItem['value'] ?: null,
                        'keterangan' => $editItem['keterangan'] ?: null,
                    ]);
                } else {
                    FormPerawatanItem::where('id', $editItem['id'])->update([
                        'status' => $editItem['status'] ?: null,
                        'keterangan' => $editItem['keterangan'] ?: null,
                    ]);
                }
            }

            DB::commit();

            // Reload form data
            $this->reloadForm();
            $this->editing = false;
            $this->dispatch('edit-saved');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Gagal menyimpan perubahan: '.$e->getMessage());
        }
    }

    private function reloadForm(): void
    {
        if ($this->formType === 'pemeriksaan') {
            $this->pemeriksaanForm = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'items', 'approvals'])
                ->findOrFail($this->formId);
        } else {
            $this->perawatanForm = FormPerawatan::with(['teknisi', 'pengguna', 'asset', 'items', 'approvals'])
                ->findOrFail($this->formId);
        }
    }

    public function updateEditItem(int $index, string $field, string $value): void
    {
        if (isset($this->editItems[$index])) {
            $this->editItems[$index][$field] = $value;
        }
    }

    // ─── Approval ─────────────────────────────────────────

    public function approveForm(string $signaturePath): void
    {
        if (! $this->canApprove) {
            $this->dispatch('error', message: 'Anda tidak memiliki akses untuk approve.');

            return;
        }

        if ($this->approvalLevel === ApprovalLevel::DiketahuiOleh->value && $this->signerMode === 'custom' && empty($this->customSignerName)) {
            $this->dispatch('error', message: 'Nama penandatangan harus diisi.');

            return;
        }

        // Auto-save edits before approving if in edit mode
        if ($this->editing) {
            $this->saveEdits();
        }

        $form = $this->getForm();

        DB::beginTransaction();

        try {
            $approval = $form->approvals()
                ->where('approval_level', $this->approvalLevel)
                ->first();

            $userId = null;
            $customName = null;

            if ($this->approvalLevel === ApprovalLevel::DiketahuiOleh->value) {
                if ($this->signerMode === 'me') {
                    $userId = Auth::id();
                } else {
                    $customName = $this->customSignerName;
                }
            } else {
                $userId = Auth::id();
            }

            if (! $approval) {
                $approval = FormApproval::create([
                    'approvable_type' => $this->formType === 'pemeriksaan' ? FormPemeriksaan::class : FormPerawatan::class,
                    'approvable_id' => $form->id,
                    'approval_level' => $this->approvalLevel,
                    'user_id' => $userId,
                    'custom_signer_name' => $customName,
                    'status' => 'pending',
                ]);
            }

            $approval->update([
                'status' => 'approved',
                'user_id' => $userId,
                'custom_signer_name' => $customName,
                'signature_path' => $signaturePath,
                'catatan' => $this->catatan ?: null,
                'approved_at' => now(),
            ]);

            $newStatus = match ($this->approvalLevel) {
                ApprovalLevel::DiketahuiOleh->value => FormStatus::Disetujui->value,
                ApprovalLevel::DisetujuiOleh->value => FormStatus::Selesai->value,
                default => $form->status,
            };

            $form->update(['status' => $newStatus]);

            $type = $this->formType === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan';
            ActivityLogger::log('approve', "Menyetujui form {$type}: {$this->formId}", $this->formType === 'pemeriksaan' ? 'App\Models\FormPemeriksaan' : 'App\Models\FormPerawatan', $this->formId);

            if ($this->approvalLevel === ApprovalLevel::DiketahuiOleh->value) {
                $this->sendNextApprovalNotification($form);
            }

            DB::commit();

            $this->saved = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Gagal approve: '.$e->getMessage());
        }
    }

    private function sendNextApprovalNotification($form): void
    {
        $approvers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['supervisor_it', 'manager_it', 'admin']);
        })->get();

        $notifClass = ApprovalRequestNotification::class;

        foreach ($approvers as $approver) {
            $approver->notify(new $notifClass(
                formType: $this->formType,
                formId: $form->id,
                nomorForm: $form->nomor_form,
                approvalLevel: ApprovalLevel::DisetujuiOleh->value,
                submittedBy: $form->teknisi->name,
                deviceName: $form->asset->nama_perangkat,
            ));
        }
    }

    public function rejectForm(): void
    {
        if (! $this->canApprove) {
            $this->dispatch('error', message: 'Anda tidak memiliki akses untuk reject.');

            return;
        }

        if (empty($this->rejectReason)) {
            $this->dispatch('error', message: 'Alasan reject harus diisi.');

            return;
        }

        $form = $this->getForm();

        DB::beginTransaction();

        try {
            $approval = $form->approvals()
                ->where('approval_level', $this->approvalLevel)
                ->first();

            if ($approval) {
                $approval->update([
                    'status' => 'rejected',
                    'catatan' => $this->rejectReason,
                    'approved_at' => now(),
                ]);
            }

            $form->update(['status' => FormStatus::Revisi->value]);

            $type = $this->formType === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan';
            ActivityLogger::log('reject', "Merevisi form {$type}: {$this->formId}", $this->formType === 'pemeriksaan' ? 'App\Models\FormPemeriksaan' : 'App\Models\FormPerawatan', $this->formId);

            DB::commit();

            $this->showRejectModal = false;
            $this->rejected = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', message: 'Gagal reject: '.$e->getMessage());
        }
    }

    public function toggleRejectModal(): void
    {
        $this->showRejectModal = ! $this->showRejectModal;
    }

    public function setSignerMode(string $mode): void
    {
        $this->signerMode = $mode;
        if ($mode === 'me') {
            $this->customSignerName = '';
            $this->signerResults = [];
            $this->showSignerDropdown = false;
        }
    }

    public function searchSigner(): void
    {
        if (strlen($this->customSignerName) < 2) {
            $this->signerResults = [];
            $this->showSignerDropdown = false;

            return;
        }

        $this->signerResults = Employee::where('status', Employee::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->customSignerName}%")
                    ->orWhere('nik', 'like', "%{$this->customSignerName}%")
                    ->orWhere('email', 'like', "%{$this->customSignerName}%");
            })
            ->limit(10)
            ->get()
            ->toArray();

        $this->showSignerDropdown = count($this->signerResults) > 0;
    }

    public function selectSigner(array $user): void
    {
        $this->customSignerName = $user['name'];
        $this->showSignerDropdown = false;
    }

    public function clearSigner(): void
    {
        $this->customSignerName = '';
        $this->signerResults = [];
        $this->showSignerDropdown = false;
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'baik' => 'Baik',
            'tidak_baik' => 'Tidak Baik',
            'good' => 'Good',
            'fair' => 'Fair',
            'critical' => 'Critical',
            'poor' => 'Poor',
            'baru' => 'Baru',
            'lama' => 'Lama',
            default => $status,
        };
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'baik', 'good', 'baru' => 'text-emerald-400',
            'fair' => 'text-blue-400',
            'critical' => 'text-amber-400',
            'tidak_baik', 'poor' => 'text-red-400',
            default => 'text-secondary',
        };
    }

    public function getDisetujuiApprovers(): Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['supervisor_it', 'manager_it']);
        })->get();
    }

    public function getSignerDisplayName(FormApproval $approval): string
    {
        if ($approval->custom_signer_name) {
            return $approval->custom_signer_name;
        }

        return $approval->user->name ?? '-';
    }

    public function render()
    {
        return view('livewire.approval.review-form')->layout('components.app-layout');
    }
}
