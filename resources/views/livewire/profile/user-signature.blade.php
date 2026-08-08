<div x-data x-init="$wire.on('signatureSaved', () => setTimeout(() => $wire.dismissMessage(), 4000)); $wire.on('signatureDeleted', () => setTimeout(() => $wire.dismissMessage(), 4000)); $wire.on('signatureError', () => setTimeout(() => $wire.dismissMessage(), 4000));">
    @if($showSuccess)
        <div class="mb-4 p-3 rounded-lg text-sm flex items-center justify-between"
            style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e;">
            <span>{{ $message }}</span>
            <button wire:click="dismissMessage" class="ml-2 text-current opacity-60 hover:opacity-100">&times;</button>
        </div>
    @endif

    @if($showError)
        <div class="mb-4 p-3 rounded-lg text-sm flex items-center justify-between"
            style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;">
            <span>{{ $message }}</span>
            <button wire:click="dismissMessage" class="ml-2 text-current opacity-60 hover:opacity-100">&times;</button>
        </div>
    @endif

    @if($hasSignature)
        <div class="space-y-3">
            <p class="text-xs text-muted">Tanda tangan Anda yang tersimpan:</p>
            <div class="rounded-lg overflow-hidden border flex items-center justify-center p-4" style="border-color: var(--color-border); background: var(--color-bg-secondary); min-height: 120px;">
                <img src="{{ $currentSignature }}" alt="Tanda Tangan" class="max-h-24 object-contain">
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs text-muted">Tanda tangan ini akan digunakan otomatis saat Anda menandatangani form.</p>
                <button wire:click="deleteSignature" type="button"
                    class="text-xs text-red-400 hover:text-red-300 whitespace-nowrap ml-2"
                    onclick="return confirm('Yakin ingin menghapus tanda tangan?')">
                    Hapus
                </button>
            </div>
        </div>
    @else
        <div x-data="{
            mode: 'draw',
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
                $wire.saveSignature(this.uploadedPreview);
            },

            canvas: null,
            ctx: null,
            drawing: false,
            lastX: 0,
            lastY: 0,
            canvasReady: false,

            initCanvas() {
                this.canvas = this.$refs.signatureCanvas;
                if (!this.canvas) return;
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
                this.canvas.height = 160;
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
                if (!this.canvasReady) this.initCanvas();
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

            stopDraw() { this.drawing = false; },

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
                $wire.saveSignature(this.canvas.toDataURL('image/png'));
            }
        }" class="space-y-3">
            <p class="text-xs text-muted">Buat tanda tangan Anda untuk digunakan saat menandatangani form.</p>

            {{-- Tab Mode --}}
            <div class="flex rounded-lg overflow-hidden border" style="border-color: var(--color-border);">
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

            {{-- Draw Mode --}}
            <div x-show="mode === 'draw'" x-cloak>
                <div class="rounded-lg overflow-hidden border-2" style="border-color: var(--color-border);">
                    <canvas x-ref="signatureCanvas"
                        class="w-full cursor-crosshair touch-none"
                        style="background: var(--color-bg-secondary); height: 160px;"
                        @mousedown="startDraw($event)" @mousemove="draw($event)" @mouseup="stopDraw()" @mouseleave="stopDraw()"
                        @touchstart.prevent="startDraw($event)" @touchmove.prevent="draw($event)" @touchend="stopDraw()">
                    </canvas>
                </div>
                <div class="flex gap-2 mt-3">
                    <button @click="clear()" type="button" class="glass-button-secondary text-sm flex-1">Hapus</button>
                    <button @click="save()" type="button" class="glass-button-primary text-sm flex-1">
                        <span wire:loading.remove wire:target="saveSignature">Simpan Tanda Tangan</span>
                        <span wire:loading wire:target="saveSignature">Menyimpan...</span>
                    </button>
                </div>
            </div>

            {{-- Upload Mode --}}
            <div x-show="mode === 'upload'" x-cloak>
                <template x-if="!uploadedPreview">
                    <div class="space-y-2">
                        <label class="flex flex-col items-center justify-center w-full h-36 rounded-lg border-2 border-dashed cursor-pointer transition-colors duration-200"
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
                        <div class="rounded-lg overflow-hidden border-2 flex items-center justify-center p-4" style="border-color: var(--color-border); background: var(--color-bg-secondary); min-height: 120px;">
                            <img :src="uploadedPreview" alt="Preview Tanda Tangan" class="max-h-24 object-contain">
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span class="text-muted">Ukuran: <span class="font-semibold text-primary" x-text="(uploadedFileSize / 1024).toFixed(1) + ' KB'"></span></span>
                            <span class="text-muted">Maksimal: 1 MB</span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="clearUpload()" type="button" class="glass-button-secondary text-sm flex-1">Hapus</button>
                            <button @click="saveUpload()" type="button" class="glass-button-primary text-sm flex-1">
                                <span wire:loading.remove wire:target="saveSignature">Simpan Tanda Tangan</span>
                                <span wire:loading wire:target="saveSignature">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    @endif
</div>
