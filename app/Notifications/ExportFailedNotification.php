<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $errorMessage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeName = $this->type === 'pemeriksaan' ? 'Pemeriksaan' : 'Perawatan';

        return (new MailMessage)
            ->subject("Export {$typeName} Gagal")
            ->line("Terjadi kesalahan saat memproses export PDF {$typeName}.")
            ->line("Error: {$this->errorMessage}")
            ->action('Coba Lagi', url(route('admin.dashboard')));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'export_failed',
            'export_type' => $this->type,
            'error' => $this->errorMessage,
            'message' => "Export {$this->type} gagal: {$this->errorMessage}",
        ];
    }
}
