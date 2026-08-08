<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserSignature extends Component
{
    public ?string $currentSignature = null;
    public bool $hasSignature = false;
    public bool $showSuccess = false;
    public bool $showError = false;
    public string $message = '';

    public function mount(): void
    {
        $this->loadSignature();
    }

    private function loadSignature(): void
    {
        $user = Auth::user();
        $this->currentSignature = $user->signature_path;
        $this->hasSignature = !empty($user->signature_path);
    }

    public function saveSignature(string $signaturePath): void
    {
        if ($this->hasSignature) {
            $this->showSuccess = false;
            $this->showError = true;
            $this->message = 'Anda sudah memiliki tanda tangan. Hapus tanda tangan yang ada terlebih dahulu sebelum menyimpan yang baru.';
            $this->dispatch('signatureError');
            return;
        }

        $user = Auth::user();
        $user->update(['signature_path' => $signaturePath]);

        $this->loadSignature();

        $this->showError = false;
        $this->showSuccess = true;
        $this->message = 'Tanda tangan berhasil disimpan.';
        $this->dispatch('signatureSaved');
    }

    public function deleteSignature(): void
    {
        $user = Auth::user();
        $user->update(['signature_path' => null]);

        $this->loadSignature();

        $this->showError = false;
        $this->showSuccess = true;
        $this->message = 'Tanda tangan berhasil dihapus.';
        $this->dispatch('signatureDeleted');
    }

    public function dismissMessage(): void
    {
        $this->showSuccess = false;
        $this->showError = false;
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.profile.user-signature');
    }
}
