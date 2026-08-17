<div>
    @if($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"
            x-data x-on:keydown.escape.window="$wire.closePopup()">
            <div class="glass-card p-6 w-full max-w-2xl space-y-5 max-h-[90vh] overflow-y-auto" @click.away="$wire.closePopup()">

                {{-- Header --}}
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-primary">{{ __('Struktur Organisasi') }}</h3>
                        <p class="text-sm text-muted mt-0.5">{{ $employee->name }} · {{ $employee->nik }}</p>
                    </div>
                    <button wire:click="closePopup" type="button"
                        class="p-1.5 rounded-lg transition-colors duration-200"
                        style="color: var(--color-text-secondary);"
                        title="{{ __('Tutup') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Tree View --}}
                <div class="rounded-lg p-4" style="background: var(--color-bg-tertiary);">
                    @if(count($hierarchyTree) > 0)
                        <ul class="space-y-1 min-w-max">
                            @foreach($hierarchyTree as $node)
                                @include('livewire.admin.structure-organization._tree-node', ['node' => $node])
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-muted text-center py-4">{{ __('Pilih directorat untuk melihat hirarki.') }}</p>
                    @endif
                </div>

                {{-- Edit Form --}}
                <div class="border-t pt-4 space-y-4" style="border-color: var(--color-border);">
                    <h4 class="text-sm font-bold text-primary">{{ __('Ubah Struktur') }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">{{ __('Directorat') }}</label>
                            <select wire:model.live="directorate_id"
                                class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">{{ __('Pilih Directorat') }}</option>
                                @foreach($this->getDirectorateList() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">{{ __('Divisi') }}</label>
                            <select wire:model.live="divisi_id" @disabled(!$directorate_id)
                                class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">{{ __('Pilih Divisi') }}</option>
                                @foreach($this->getDivisiList() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">{{ __('Departemen') }}</label>
                            <select wire:model.live="departement_id" @disabled(!$divisi_id)
                                class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">{{ __('Pilih Departemen') }}</option>
                                @foreach($this->getDepartementList() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted mb-1">{{ __('Sub Departemen') }}</label>
                            <select wire:model.live="sub_departement_id" @disabled(!$departement_id)
                                class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">{{ __('Pilih Sub Departemen') }}</option>
                                @foreach($this->getSubDepartementList() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-muted mb-1">{{ __('Position') }}</label>
                            <select wire:model="position_id"
                                class="w-full px-3 py-2 rounded-lg text-sm transition-colors duration-200"
                                style="background: var(--color-input-bg, var(--color-glass-bg)); border: 1px solid var(--color-border); color: var(--color-text-primary);">
                                <option value="">{{ __('Pilih Position') }}</option>
                                @foreach($this->getPositionList() as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2 border-t" style="border-color: var(--color-border);">
                    <button wire:click="save" wire:loading.attr="disabled" type="button"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                        style="background: var(--color-primary); color: var(--color-button-text);">
                        <span wire:loading.remove wire:target="save">{{ __('Simpan') }}</span>
                        <span wire:loading wire:target="save">{{ __('Menyimpan') }}...</span>
                    </button>
                    <button wire:click="closePopup" type="button"
                        class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200"
                        style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
                        {{ __('Batal') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
