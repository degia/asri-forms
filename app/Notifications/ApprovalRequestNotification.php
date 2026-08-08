<?php

namespace App\Notifications;

use App\Enums\ApprovalLevel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $formType,
        public readonly int $formId,
        public readonly string $nomorForm,
        public readonly string $approvalLevel,
        public readonly string $submittedBy,
        public readonly string $deviceName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $levelLabel = match ($this->approvalLevel) {
            ApprovalLevel::DiketahuiOleh->value => 'Diketahui Oleh',
            ApprovalLevel::DisetujuiOleh->value => 'Disetujui Oleh',
            default => $this->approvalLevel,
        };

        return [
            'form_type' => $this->formType,
            'form_id' => $this->formId,
            'nomor_form' => $this->nomorForm,
            'approval_level' => $this->approvalLevel,
            'submitted_by' => $this->submittedBy,
            'device_name' => $this->deviceName,
            'message' => "Form {$this->nomorForm} menunggu approval Anda sebagai \"{$levelLabel}\"",
        ];
    }
}
