<?php

use App\Enums\ApprovalLevel;
use App\Enums\FormStatus;
use App\Models\FormApproval;
use App\Models\FormPemeriksaan;
use App\Models\User;
use App\Notifications\ApprovalRequestNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component
{
    public ?FormPemeriksaan $form = null;
    public string $status = '';
    public string $catatan = '';
    public bool $saved = false;
    public ?string $userSignature = null;

    public function mount(string $id): void
    {
        $this->form = FormPemeriksaan::with(['teknisi', 'pengguna', 'asset', 'items', 'approvals'])
            ->findOrFail($id);

        $this->authorizeSigning();

        $approval = $this->form->approvals()
            ->where('approval_level', ApprovalLevel::DiperiksaOleh)
            ->first();

        if ($approval) {
            $this->status = $approval->status;
        }

        $this->userSignature = Auth::user()->signature_path;
    }

    private function authorizeSigning(): void
    {
        $user = Auth::user();

        if ($this->form->status !== FormStatus::Submitted->value) {
            abort(403, 'Form hanya dapat ditandatangani pada status Submitted.');
        }

        if ($user->hasRole('manager_it')) {
            abort(403, 'Role Manager tidak dapat menandatangani form berstatus Submitted.');
        }

        if ($this->form->user_id !== $user->email) {
            abort(403, 'Hanya pembuat form yang dapat menandatangani form ini.');
        }
    }

    public function approve(string $signaturePath): void
    {
        $user = Auth::user();

        if ($this->form->status !== FormStatus::Submitted->value
            || $user->hasRole('manager_it')
            || $this->form->user_id !== $user->email) {
            $this->dispatch('error', message: 'Anda tidak memiliki akses untuk menandatangani form ini.');

            return;
        }

        $approval = $this->form->approvals()
            ->where('approval_level', ApprovalLevel::DiperiksaOleh)
            ->first();

        if (!$approval) {
            $approval = FormApproval::create([
                'approvable_type' => FormPemeriksaan::class,
                'approvable_id' => $this->form->id,
                'approval_level' => ApprovalLevel::DiperiksaOleh,
                'user_id' => Auth::id(),
                'status' => 'pending',
            ]);
        }

        $approval->update([
            'status' => 'approved',
            'signature_path' => $signaturePath,
            'catatan' => $this->catatan ?: null,
            'approved_at' => now(),
        ]);

        $this->form->update(['status' => FormStatus::Diketahui->value]);

        $this->sendDiketahuiNotification();

        $this->saved = true;
    }

    private function sendDiketahuiNotification(): void
    {
        $pengguna = $this->form->pengguna;
        if ($pengguna) {
            $pengguna->notify(new ApprovalRequestNotification(
                formType: 'pemeriksaan',
                formId: $this->form->id,
                nomorForm: $this->form->nomor_form,
                approvalLevel: ApprovalLevel::DiketahuiOleh->value,
                submittedBy: $this->form->teknisi->name,
                deviceName: $this->form->asset->nama_perangkat,
            ));
        }
    }

    public function render(): mixed
    {
        return view('livewire.pages.pemeriksaan.signature');
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-6">
    @if($saved)
        <div class="glass-card p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-emerald-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-primary mb-2">Tanda Tangan Berhasil Disimpan</h2>
            <p class="text-sm text-muted mb-6">Form {{ $form->nomor_form }} telah ditandatangani sebagai "Diperiksa Oleh"</p>
            <a href="{{ route('dashboard') }}" wire:navigate
                class="glass-button-primary inline-block">
                Kembali ke Dashboard
            </a>
        </div>
    @else
        <h1 class="text-2xl font-bold text-primary mb-6">Tanda Tangan - Diperiksa Oleh</h1>

        <div class="glass-card p-4 mb-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div><span class="text-xs text-muted">No. Form</span><p class="font-mono font-semibold text-primary">{{ $form->nomor_form }}</p></div>
                <div><span class="text-xs text-muted">Teknisi</span><p class="text-primary">{{ $form->teknisi->name }}</p></div>
                <div><span class="text-xs text-muted">Perangkat</span><p class="text-primary">{{ $form->asset->nama_perangkat }}</p></div>
                <div><span class="text-xs text-muted">No. Asset</span><p class="font-mono text-primary">{{ $form->asset->no_asset }}</p></div>
            </div>
        </div>

        <div class="glass-card p-4 mb-4">
            <h3 class="text-sm font-semibold text-primary mb-3">Catatan (opsional)</h3>
            <textarea wire:model.live="catatan" rows="2"
                class="glass-input w-full rounded-lg px-3 py-2 text-sm resize-none"
                placeholder="Tambahkan catatan..."></textarea>
        </div>

        <div class="glass-card p-4 mb-4">
            <h3 class="text-sm font-semibold text-primary mb-3">Tanda Tangan</h3>
            <div x-data="{
                @if($userSignature)
                mode: 'paste',
                @else
                mode: 'draw',
                @endif
                userSignature: '{{ $userSignature }}',
                uploadedPreview: null,
                uploadedFile: null,
                uploadedFileSize: 0,
                maxFileSize: 1048576,

                handleUpload(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    if (!file.type.match('image/png')) {
                        alert('Hanya file PNG yang diperbolehkan');
                        e.target.value = '';
                        return;
                    }
                    if (file.size > this.maxFileSize) {
                        alert('Ukuran file melebihi 1 MB. Silakan kompres file terlebih dahulu.');
                        e.target.value = '';
                        return;
                    }
                    this.uploadedFile = file;
                    this.uploadedFileSize = file.size;
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        const img = new Image();
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            const maxWidth = 800, maxHeight = 400;
                            let w = img.width, h = img.height;
                            if (w > maxWidth) { h = (h * maxWidth) / w; w = maxWidth; }
                            if (h > maxHeight) { w = (w * maxHeight) / h; h = maxHeight; }
                            canvas.width = w;
                            canvas.height = h;
                            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                            this.uploadedPreview = canvas.toDataURL('image/png');
                        };
                        img.src = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                clearUpload() {
                    this.uploadedPreview = null;
                    this.uploadedFile = null;
                    this.uploadedFileSize = 0;
                    this.$refs.uploadInput.value = '';
                },

                saveUpload() {
                    if (!this.uploadedPreview) {
                        alert('Harap unggah file tanda tangan terlebih dahulu');
                        return;
                    }
                    $wire.approve(this.uploadedPreview);
                },

                canvas: null,
                ctx: null,
                drawing: false,
                lastX: 0,
                lastY: 0,
                canvasReady: false,

                init() {
                    this.canvas = this.$refs.signatureCanvas;
                    this.ctx = this.canvas.getContext('2d');
                    this.resize();
                    this.canvasReady = true;
                    window.addEventListener('resize', () => this.resize());
                },

                resize() {
                    if (!this.canvas || !this.canvas.parentElement) return;
                    const rect = this.canvas.parentElement.getBoundingClientRect();
                    if (rect.width === 0) return;
                    this.canvas.width = rect.width;
                    this.canvas.height = 200;
                    this.ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--color-text-primary').trim();
                    this.ctx.lineWidth = 2;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                },

                getCoords(e) {
                    const rect = this.canvas.getBoundingClientRect();
                    const touch = e.touches ? e.touches[0] : e;
                    return {
                        x: (touch.clientX - rect.left) * (this.canvas.width / rect.width),
                        y: (touch.clientY - rect.top) * (this.canvas.height / rect.height)
                    };
                },

                startDraw(e) {
                    if (!this.canvasReady) this.init();
                    if (this.canvas.width === 0) this.resize();
                    this.drawing = true;
                    const coords = this.getCoords(e);
                    this.lastX = coords.x;
                    this.lastY = coords.y;
                },

                draw(e) {
                    if (!this.drawing) return;
                    const coords = this.getCoords(e);

                    this.ctx.beginPath();
                    this.ctx.moveTo(this.lastX, this.lastY);
                    this.ctx.lineTo(coords.x, coords.y);
                    this.ctx.stroke();

                    this.lastX = coords.x;
                    this.lastY = coords.y;
                },

                stopDraw() {
                    this.drawing = false;
                },

                clear() {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                },

                isEmpty() {
                    const pixel = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
                    return !pixel.some(v => v !== 0);
                },

                save() {
                    if (this.isEmpty()) {
                        alert('Harap tanda tangan terlebih dahulu');
                        return;
                    }
                    const dataUrl = this.canvas.toDataURL('image/png');
                    $wire.approve(dataUrl);
                }
            }" class="space-y-3">
                {{-- Tab Mode --}}
                <div class="flex rounded-lg overflow-hidden border" style="border-color: var(--color-border);">
                    @if($userSignature)
                    <button @click="mode = 'paste'" type="button"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-center transition-colors duration-200"
                        :style="mode === 'paste' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);'">
                        Saya
                    </button>
                    @endif
                    <button @click="mode = 'draw'" type="button"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-center transition-colors duration-200"
                        :style="mode === 'draw' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);'">
                        Gambar
                    </button>
                    <button @click="mode = 'upload'" type="button"
                        class="flex-1 px-3 py-1.5 text-xs font-medium text-center transition-colors duration-200"
                        :style="mode === 'upload' ? 'background: var(--color-primary); color: var(--color-button-text);' : 'background: var(--color-glass-bg); color: var(--color-text-secondary);'">
                        Upload PNG
                    </button>
                </div>

                {{-- Paste Mode --}}
                @if($userSignature)
                <div x-show="mode === 'paste'" x-cloak>
                    <div class="space-y-3">
                        <p class="text-xs text-muted">Gunakan tanda tangan yang tersimpan di profil Anda.</p>
                        <div class="rounded-lg overflow-hidden border-2 flex items-center justify-center p-4" style="border-color: var(--color-border); background: var(--color-bg-secondary); min-height: 160px;">
                            <img :src="userSignature" alt="Tanda Tangan Profil" class="max-h-32 object-contain">
                        </div>
                        <button @click="$wire.approve(userSignature)" type="button"
                            class="w-full glass-button-primary text-sm">
                            <span wire:loading.remove wire:target="approve">Gunakan Tanda Tangan Ini</span>
                            <span wire:loading wire:target="approve">Menyimpan...</span>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Draw Mode --}}
                <div x-show="mode === 'draw'" x-cloak>
                    <div class="rounded-lg overflow-hidden border-2" style="border-color: var(--color-border);">
                        <canvas x-ref="signatureCanvas"
                            class="w-full cursor-crosshair touch-none"
                            style="background: var(--color-bg-secondary); height: 200px;"
                            @mousedown="startDraw($event)" @mousemove="draw($event)" @mouseup="stopDraw()" @mouseleave="stopDraw()"
                            @touchstart.prevent="startDraw($event)" @touchmove.prevent="draw($event)" @touchend="stopDraw()">
                        </canvas>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button @click="clear()" type="button" class="glass-button-secondary text-sm flex-1">Hapus</button>
                        <button @click="save()" type="button" class="glass-button-primary text-sm flex-1">
                            <span wire:loading.remove wire:target="approve">Simpan Tanda Tangan</span>
                            <span wire:loading wire:target="approve">Menyimpan...</span>
                        </button>
                    </div>
                </div>

                {{-- Upload Mode --}}
                <div x-show="mode === 'upload'" x-cloak>
                    <template x-if="!uploadedPreview">
                        <div class="space-y-2">
                            <label class="flex flex-col items-center justify-center w-full h-40 rounded-lg border-2 border-dashed cursor-pointer transition-colors duration-200"
                                style="border-color: var(--color-border); background: var(--color-bg-secondary);"
                                @dragover.prevent="$el.style.borderColor = 'var(--color-primary)'"
                                @dragleave.prevent="$el.style.borderColor = 'var(--color-border)'"
                                @drop.prevent="$el.style.borderColor = 'var(--color-border)'; handleUpload({target: {files: $event.dataTransfer.files}})">
                                <svg class="w-8 h-8 mb-2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs text-muted">Klik atau seret file PNG ke sini</span>
                                <input x-ref="uploadInput" type="file" accept="image/png" class="hidden" @change="handleUpload($event)">
                            </label>
                            <p class="text-xs text-muted text-center">Format: PNG | Maksimal: 1 MB</p>
                        </div>
                    </template>
                    <template x-if="uploadedPreview">
                        <div class="space-y-3">
                            <div class="rounded-lg overflow-hidden border-2 flex items-center justify-center p-4" style="border-color: var(--color-border); background: var(--color-bg-secondary); min-height: 160px;">
                                <img :src="uploadedPreview" alt="Preview Tanda Tangan" class="max-h-32 object-contain">
                            </div>
                            <div class="flex items-center justify-between text-xs px-1">
                                <span class="text-muted">Ukuran: <span class="font-semibold text-primary" x-text="(uploadedFileSize / 1024).toFixed(1) + ' KB'"></span></span>
                                <span class="text-muted">Maksimal: 1 MB</span>
                            </div>
                            <div class="flex gap-2">
                                <button @click="clearUpload()" type="button" class="glass-button-secondary text-sm flex-1">Hapus</button>
                                <button @click="saveUpload()" type="button" class="glass-button-primary text-sm flex-1">
                                    <span wire:loading.remove wire:target="approve">Simpan Tanda Tangan</span>
                                    <span wire:loading wire:target="approve">Menyimpan...</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif
</div>
