<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $path,
        public string $filename,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeName = $this->type === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan';

        return (new MailMessage)
            ->subject("Export {$typeName} Siap Diunduh")
            ->line("File export PDF {$typeName} Anda sudah selesai diproses.")
            ->line("Nama file: {$this->filename}")
            ->action('Download', url(route('admin.dashboard')))
            ->line('File akan tersedia selama 24 jam.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'export_ready',
            'export_type' => $this->type,
            'filename' => $this->filename,
            'path' => $this->path,
            'message' => "Export {$this->type} sudah siap diunduh.",
        ];
    }
}
