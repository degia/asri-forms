<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.app-layout')] class extends Component {}; ?>

<div class="max-w-7xl mx-auto px-4 py-6 flex flex-col h-dvh" x-data
    @form-deleted.window="window.location.reload()">
    <h1 class="text-2xl font-bold text-primary shrink-0 mb-4">Search & Filters Data Form</h1>

    {{-- Filters --}}
    <div class="shrink-0 mb-4 glass-card px-3 py-2">
        <div class="flex flex-wrap items-end gap-2">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] text-muted">Search</label>
                <input type="text" wire:model.live="search" placeholder="No. form, teknisi, perangkat..."
                    class="glass-input w-full rounded-lg px-2 py-1.5 text-xs">
            </div>

            {{-- Form Type --}}
            <div class="w-[130px]">
                <label class="text-[10px] text-muted">Type Forms</label>
                <select wire:model.live="formType" class="glass-input w-full rounded-lg px-2 py-1.5 text-xs">
                    <option value="">Semua</option>
                    <option value="pemeriksaan">Pemeriksaan</option>
                    <option value="perawatan">Perawatan</option>
                </select>
            </div>

            {{-- Status --}}
            <div class="w-[120px]">
                <label class="text-[10px] text-muted">Status</label>
                <select wire:model.live="status" class="glass-input w-full rounded-lg px-2 py-1.5 text-xs">
                    <option value="">Semua</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="diketahui">Diketahui</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="selesai">Selesai</option>
                    <option value="revisi">Revisi</option>
                </select>
            </div>

            {{-- Kondisi --}}
            <div class="w-[110px]">
                <label class="text-[10px] text-muted">Kondisi</label>
                <select wire:model.live="kondisi" class="glass-input w-full rounded-lg px-2 py-1.5 text-xs">
                    <option value="">Semua</option>
                    <option value="baru">Baru</option>
                    <option value="lama">Lama</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="critical">Critical</option>
                    <option value="poor">Poor</option>
                </select>
            </div>

            {{-- User --}}
            <div class="relative w-[160px]">
                <label class="text-[10px] text-muted">Teknisi</label>
                <input type="text" wire:model.live="userSearch" wire:input="searchUser"
                    placeholder="Nama / NIK..."
                    class="glass-input w-full rounded-lg px-2 py-1.5 text-xs"
                    @focus="$wire.set('showUserDropdown', true)"
                    @click.away="$wire.set('showUserDropdown', false)">
                @if ($userId)
                    <button wire:click="selectUser()" class="absolute right-1.5 bottom-1.5 text-[10px] text-red-400">clear</button>
                @endif
                @if ($showUserDropdown && count($userResults) > 0)
                    <div class="absolute z-20 w-full mt-1 rounded-lg max-h-40 overflow-y-auto"
                        style="background: var(--color-card-bg); border: 1px solid var(--color-card-border);">
                        @foreach ($userResults as $user)
                            <button wire:click="selectUser('{{ $user['email'] }}')"
                                class="w-full text-left px-2 py-1.5 text-xs transition-colors text-primary" onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'" onmouseout="this.style.backgroundColor=''">
                                {{ $user['name'] }} <span class="text-muted">({{ $user['nik'] ?? '-' }})</span>
                            </button> @endforeach
    </div>
    @endif
</div>

{{-- Date From --}}
<div class="w-[140px]">
    <label class="text-[10px] text-muted">Dari</label>
    <input type="date" wire:model.live="dateFrom" class="glass-input w-full rounded-lg px-2 py-1.5 text-xs">
</div>

{{-- Date To --}}
<div class="w-[140px]">
    <label class="text-[10px] text-muted">Sampai</label>
    <input type="date" wire:model.live="dateTo" class="glass-input w-full rounded-lg px-2 py-1.5 text-xs">
</div>

{{-- Reset --}}
<div>
    <button wire:click="resetFilters" class="glass-button-secondary text-xs px-3 py-1.5 whitespace-nowrap">
        Reset
    </button>
</div>
</div>
</div>

{{-- Results --}}
<div class="glass-card pt-0 px-4 pb-4 flex-1 overflow-auto min-h-0">

    @if (count($results) === 0)
        <div class="text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--color-text-muted);" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="text-sm text-muted">Tidak ada form ditemukan</p>
        </div>
    @else
        <table class="w-full min-w-max text-sm">
            <thead class="sticky top-0 z-20"
                style="background: var(--color-bg-primary); box-shadow: inset 0 -1px 0 var(--color-border);">
                <tr class="border-b whitespace-nowrap" style="border-color: var(--color-border);">
                    <th wire:click="toggleSort('nomor_form')"
                        class="text-left py-2 px-3 text-xs text-muted font-medium cursor-pointer hover:text-primary whitespace-nowrap">
                        No. Form @if ($sortBy === 'nomor_form')
                            {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Tipe</th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Teknisi</th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Pengguna</th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Perangkat</th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">No. Asset</th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Kondisi</th>
                    <th wire:click="toggleSort('status')"
                        class="text-left py-2 px-3 text-xs text-muted font-medium cursor-pointer hover:text-primary whitespace-nowrap">
                        Status @if ($sortBy === 'status')
                            {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Disetujui</th>
                    <th wire:click="toggleSort('submitted_at')"
                        class="text-left py-2 px-3 text-xs text-muted font-medium cursor-pointer hover:text-primary whitespace-nowrap">
                        Tanggal @if ($sortBy === 'submitted_at')
                            {{ $sortDir === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th class="text-left py-2 px-3 text-xs text-muted font-medium whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: color-mix(in srgb, var(--color-border) 25%, transparent);">
                @foreach ($results as $form)
                    <tr class="transition-colors whitespace-nowrap"
                        style="background-color: {{ $this->getStatusBg($form['status']) }}"
                        onmouseover="this.style.backgroundColor='var(--color-bg-tertiary)'"
                        onmouseout="this.style.backgroundColor='{{ $this->getStatusBg($form['status']) }}'">
                        <td class="py-2.5 px-3 font-mono font-semibold text-primary text-xs whitespace-nowrap">
                            @if ($form['status'] !== 'draft')
                                <a href="{{ route('approval.show', ['type' => $form['type'], 'id' => $form['id']]) }}"
                                    wire:navigate
                                    class="hover:text-blue-400 transition-colors">{{ $form['nomor_form'] }}</a>
                            @else
                                {{ $form['nomor_form'] }}
                            @endif
                        </td>
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        {{ $form['type'] === 'pemeriksaan' ? 'bg-blue-500/15 text-blue-400' : 'bg-purple-500/15 text-purple-400' }}">
                                {{ ucfirst($form['type']) }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-primary whitespace-nowrap">{{ $form['teknisi'] }}</td>
                        <td class="py-2.5 px-3 text-primary whitespace-nowrap">{{ $form['pengguna'] }}</td>
                        <td class="py-2.5 px-3 text-primary whitespace-nowrap">{{ $form['perangkat'] }}</td>
                        <td class="py-2.5 px-3 font-mono text-xs text-primary whitespace-nowrap">
                            @if ($form['asset_id'])
                                <a href="{{ route('assets.show', $form['asset_id']) }}" wire:navigate
                                    class="hover:underline">{{ $form['no_asset'] }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-xs text-secondary whitespace-nowrap">{{ $form['kondisi'] }}</td>
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $this->getStatusColor($form['status']) }}">
                                {{ ucfirst($form['status']) }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-primary whitespace-nowrap">{{ $form['disetujui'] }}</td>
                        <td class="py-2.5 px-3 text-muted text-xs whitespace-nowrap">
                            {{ $form['submitted_at_formatted'] }}</td>
                        <td class="py-2.5 px-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <button wire:click="viewForm({{ $form['id'] }}, '{{ $form['type'] }}')"
                                    class="text-blue-400 hover:text-blue-300 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                @if ($form['status'] !== 'draft')
                                    <a href="{{ route('approval.show', ['type' => $form['type'], 'id' => $form['id']]) }}"
                                        wire:navigate class="text-blue-400 hover:text-blue-300 transition-colors"
                                        title="Review">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </a>
                                @endif
                                <a href="{{ route($form['type'] . '.export-pdf', $form['id']) }}" target="_blank"
                                    class="text-emerald-400 hover:text-emerald-300 transition-colors"
                                    title="Export PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 11v4m-2-2h4" />
                                    </svg>
                                </a>
                                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teknisi'))
                                    <button wire:click="deleteForm({{ $form['id'] }}, '{{ $form['type'] }}')"
                                        wire:confirm="Yakin ingin menghapus form ini? Form yang sudah dihapus tidak dapat dikembalikan."
                                        class="text-red-400 hover:text-red-300 transition-colors" title="Hapus Form">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                                @if (in_array($form['status'], ['draft', 'submitted', 'diketahui']) &&
                                        (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teknisi')))
                                    <a href="{{ route($form['type'] . '.create') }}?formId={{ $form['id'] }}"
                                        wire:navigate class="text-yellow-400 hover:text-yellow-300 transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endif
                                @if (
                                    $form['status'] === 'submitted' &&
                                        ($form['user_id'] ?? null) === auth()->id() &&
                                        !auth()->user()->hasRole('manager_it'))
                                    <a href="{{ route($form['type'] . '.signature', $form['id']) }}" wire:navigate
                                        class="text-amber-400 hover:text-amber-300 transition-colors"
                                        title="Tanda Tangan - Diperiksa Oleh">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- View Modal --}}
@if ($viewingForm)
    @php
        $_type = $viewingForm['type'];
        $_isPemeriksaan = $_type === 'pemeriksaan';
        $_title = $_isPemeriksaan ? 'FORMULIR PEMERIKSAAN PERANGKAT' : 'FORMULIR PERAWATAN PERANGKAT';
        $_form = $viewingForm;
        $_items = $_form['items'] ?? [];
        $_hardware = array_values(array_filter($_items, fn($i) => ($i['category'] ?? '') === 'hardware'));
        $_aplikasi = array_values(array_filter($_items, fn($i) => ($i['category'] ?? '') === 'aplikasi'));
        $_os = array_values(array_filter($_items, fn($i) => ($i['category'] ?? '') === 'operating_system'));
        usort($_hardware, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
        usort($_aplikasi, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
        usort($_os, fn($a, $b) => ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0));
        $_approvals = $_form['approvals'] ?? [];
        $_diperiksa =
            current(array_filter($_approvals, fn($a) => ($a['approval_level'] ?? '') === 'diperiksa_oleh')) ?: null;
        $_diketahui =
            current(array_filter($_approvals, fn($a) => ($a['approval_level'] ?? '') === 'diketahui_oleh')) ?: null;
        $_disetujui =
            current(array_filter($_approvals, fn($a) => ($a['approval_level'] ?? '') === 'disetujui_oleh')) ?: null;
        $_sigLabel1 = $_isPemeriksaan ? 'Diperiksa Oleh' : 'Perawatan Oleh';
        $_sigRole1 = $_isPemeriksaan ? '' : 'Teknisi IT Operation';
        $_sigLabel2 = 'Diketahui Oleh';
        $_sigRole2 = $_isPemeriksaan ? '' : 'Pengguna Perangkat';
        $_sigLabel3 = 'Disetujui Oleh';
        $_sigRole3 = $_isPemeriksaan ? '' : 'Supervisor / Manager IT Operation';
        $_kondisiOpts = [
            'good' => ['label' => 'Good', 'color' => '#10b981'],
            'fair' => ['label' => 'Fair', 'color' => '#3b82f6'],
            'critical' => ['label' => 'Critical', 'color' => '#f59e0b'],
            'poor' => ['label' => 'Poor', 'color' => '#ef4444'],
            'good_normal' => ['label' => 'Good/Normal', 'color' => '#10b981'],
            'caution_poor' => ['label' => 'Caution/Poor', 'color' => '#ef4444'],
        ];
        $_selectedKondisi = $_kondisiOpts[$_form['kondisi_akhir'] ?? ''] ?? null;
    @endphp
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 overflow-y-auto"
        style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);" x-data x-on:click.self="$wire.closeView()">
        <div class="w-full max-w-5xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            x-on:click.self="$wire.closeView()">
            <div
                class="sticky top-0 z-10 flex justify-end p-2 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <button wire:click="closeView"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none px-1">&times;</button>
            </div>
            <div class="p-6 space-y-5 text-sm text-gray-800 dark:text-gray-200">

                {{-- HEADER --}}
                <table class="w-full">
                    <tr>
                        <td class="w-14 align-middle"><img src="{{ asset('images/asri.png') }}"
                                class="w-12 h-12 object-contain" alt="ASRI"></td>
                        <td class="text-center">
                            <h1 class="text-lg font-bold">{{ $_title }}</h1>
                            <p class="text-xs text-gray-500">IT Department &mdash; ASRI</p>
                        </td>
                        <td class="w-14"></td>
                    </tr>
                </table>

                {{-- NO. FORM & TANGGAL --}}
                <table class="w-full">
                    <tr>
                        <td class="font-semibold">No : {{ $_form['nomor_form'] }}</td>
                        <td class="text-right text-gray-500">Tanggal :
                            {{ $_form['submitted_at'] ? \Carbon\Carbon::parse($_form['submitted_at'])->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                </table>

                {{-- INFORMASI PENGGUNA --}}
                <div>
                    <div
                        class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">
                        Informasi Pengguna</div>
                    <table class="w-full border border-gray-300 dark:border-gray-600">
                        <tr>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[16%]">
                                Nama User</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[34%]">
                                {{ $_form['pengguna']['name'] ?? '-' }}</td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[16%]">
                                NIK User</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[34%]">
                                {{ $_form['pengguna']['nik'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                Site</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                {{ $_form['pengguna']['site'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                No. Telepon</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                {{ $_form['pengguna']['no_telepon'] ?? '-' }}</td>
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                Alamat Email</td>
                            <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                {{ $_form['pengguna']['email'] ?? '-' }}</td>
                        </tr>
                    </table>
                    @if (!$_isPemeriksaan)
                        <div class="mt-1 text-xs text-gray-500">Location Perawatan: Site:
                            {{ $_form['site_location'] ?? '-' }}, {{ $_form['location_detail'] ?? '-' }}</div>
                    @endif
                </div>

                {{-- INFORMASI PERANGKAT --}}
                <div>
                    <div
                        class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">
                        Informasi Perangkat</div>
                    @if ($_isPemeriksaan)
                        <table class="w-full border border-gray-300 dark:border-gray-600" style="text-align: center;">
                            <tr>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[12%]">
                                    Kategori</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[14%]">
                                    {{ $_form['asset']['kategori'] ?? '-' }}</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[10%]">
                                    Brand</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[14%]">
                                    {{ $_form['asset']['brand'] ?? '-' }}</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[10%]">
                                    Tipe</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[14%]">
                                    {{ $_form['asset']['tipe'] ?? '-' }}</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[13%]">
                                    Nama Perangkat</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 w-[13%]">
                                    {{ $_form['asset']['nama_perangkat'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                    No. Serial</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['asset']['no_serial'] ?? '-' }}</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                    No. Asset</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['asset']['no_asset'] ?? '-' }}</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                    Kondisi</td>
                                <td colspan="3" class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    @php
                                        $_k = $_form['kondisi'] ?? '';
                                        $_kk = $_form['kondisi_keterangan'] ?? '';
                                    @endphp
                                    @if ($_k === 'baru')
                                        <strong>BARU</strong>
                                    @elseif($_k === 'lama')
                                        LAMA{{ $_kk ? " ({$_kk})" : '' }}
                                    @elseif($_k === 'good_normal')
                                        Good / Normal{{ $_kk ? " ({$_kk})" : '' }}
                                    @elseif($_k === 'caution_poor')
                                        Caution / Poor{{ $_kk ? " ({$_kk})" : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                    Site Location</td>
                                <td colspan="3" class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['site_location'] ?? '-' }}</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold">
                                    Location Detail</td>
                                <td colspan="3" class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['location_detail'] ?? '-' }}</td>
                            </tr>
                        </table>
                    @else
                        <table class="w-full border border-gray-300 dark:border-gray-600" style="text-align: center;">
                            <tr>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[12%]">
                                    Kategori</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[10%]">
                                    Brand, Tipe</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[13%]">
                                    Nama Perangkat</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[10%]">
                                    No. Serial</td>
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 font-semibold w-[10%]">
                                    No. Asset</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['asset']['kategori'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ ($_form['asset']['brand'] ?? '') . ', ' . ($_form['asset']['tipe'] ?? '') }}
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['asset']['nama_perangkat'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['asset']['no_serial'] ?? '-' }}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5">
                                    {{ $_form['asset']['no_asset'] ?? '-' }}</td>
                            </tr>
                        </table>
                    @endif
                </div>

                {{-- CHECKLIST ITEMS --}}
                @if (count($_hardware) || count($_aplikasi) || count($_os))
                    <div>
                        <table class="w-full">
                            <tr class="align-top">
                                {{-- LEFT COL --}}
                                <td class="w-1/2 pr-2 align-top">
                                    @if (count($_hardware))
                                        <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500"
                                            style="margin-top:0;">{{ $_isPemeriksaan ? 'Pemeriksaan' : 'Perawatan' }}
                                            Hardware</div>
                                        <table class="w-full border border-gray-300 dark:border-gray-600">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-800">
                                                    <th
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">
                                                        Name</th>
                                                    <th
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">
                                                        {{ $_isPemeriksaan ? 'Kondisi' : 'Status' }}</th>
                                                    <th
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">
                                                        Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($_hardware as $_item)
                                                    <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                        <td
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                            {{ $_item['name'] }}</td>
                                                        <td
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                            @if (($_item['status'] ?? '') === 'baik')
                                                                Baik
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik')
                                                                Tidak Baik
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                            {{ $_item['keterangan'] ?? '' }}</td>
                                                    </tr>
                                                    @if (
                                                        $_isPemeriksaan &&
                                                            ($_item['name'] ?? '') === 'Battery' &&
                                                            (!empty($_item['full_charge_capacity']) || !empty($_item['design_capacity'])))
                                                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                                                            <td colspan="3"
                                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1"
                                                                style="border-top:none;">
                                                                <table class="w-full text-[10px]">
                                                                    <tr>
                                                                        <td
                                                                            class="font-semibold text-right pr-1 w-[25%]">
                                                                            Full Charge Capacity</td>
                                                                        <td class="w-[25%]">
                                                                            {{ $_item['full_charge_capacity'] ?? '-' }}
                                                                            mWh</td>
                                                                        <td
                                                                            class="font-semibold text-right pr-1 w-[25%]">
                                                                            Design Capacity</td>
                                                                        <td class="w-[25%]">
                                                                            {{ $_item['design_capacity'] ?? '-' }} mWh
                                                                        </td>
                                                                        <td
                                                                            class="font-semibold text-right pr-1 w-[25%]">
                                                                            Battery Health</td>
                                                                        <td class="font-semibold w-[25%]">
                                                                            @if (!empty($_item['full_charge_capacity']) && !empty($_item['design_capacity']) && $_item['design_capacity'] > 0)
                                                                                {{ round(($_item['full_charge_capacity'] / $_item['design_capacity']) * 100) }}%
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    @if ($_isPemeriksaan && count($_os))
                                        <div
                                            class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">
                                            Operating System</div>
                                        <table class="w-full border border-gray-300 dark:border-gray-600">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-800">
                                                    <th
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">
                                                        Name</th>
                                                    <th
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">
                                                        Kondisi</th>
                                                    <th
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">
                                                        Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($_os as $_item)
                                                    <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                        <td
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                            {{ $_item['name'] }}</td>
                                                        <td
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                            @if (($_item['status'] ?? '') === 'baik')
                                                                Baik
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik')
                                                                Tidak Baik
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                            {{ $_item['keterangan'] ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    @if (!$_isPemeriksaan)
                                        @if (count($_os))
                                            <div
                                                class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">
                                                Perawatan Operating Sistem</div>
                                            <table class="w-full border border-gray-300 dark:border-gray-600">
                                                <thead>
                                                    <tr class="bg-gray-100 dark:bg-gray-800">
                                                        <th
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">
                                                            Name</th>
                                                        <th
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">
                                                            Status</th>
                                                        <th
                                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">
                                                            Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($_os as $_item)
                                                        <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                            <td
                                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                                {{ $_item['name'] }}</td>
                                                            <td
                                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                                @if (($_item['status'] ?? '') === 'baik')
                                                                    Baik
                                                                @elseif(($_item['status'] ?? '') === 'tidak_baik')
                                                                    Tidak Baik
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td
                                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                                {{ $_item['keterangan'] ?? '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif

                                        <div
                                            class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">
                                            Kondisi Setelah Perawatan</div>
                                        @if ($_selectedKondisi)
                                            <div class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm font-bold"
                                                style="color: {{ $_selectedKondisi['color'] }};">
                                                <span class="inline-block w-3.5 h-3.5 rounded-full align-middle mr-1.5"
                                                    style="background: {{ $_selectedKondisi['color'] }};"></span>{{ $_selectedKondisi['label'] }}
                                            </div>
                                        @else
                                            <div
                                                class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm text-gray-400">
                                                -</div>
                                        @endif
                                    @endif
                                </td>

                                {{-- RIGHT COL --}}
                                <td class="w-1/2 pl-2 align-top">
                                    <div class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500"
                                        style="margin-top:0;">{{ $_isPemeriksaan ? 'Pemeriksaan' : 'Perawatan' }}
                                        Aplikasi</div>
                                    <table class="w-full border border-gray-300 dark:border-gray-600">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-800">
                                                <th
                                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[36%]">
                                                    Name</th>
                                                <th
                                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs font-semibold w-[20%]">
                                                    Status</th>
                                                <th
                                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-left text-xs font-semibold w-[44%]">
                                                    Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($_aplikasi as $_item)
                                                <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                                    <td
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                        {{ $_item['name'] }}</td>
                                                    <td
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-center">
                                                        @if ($_isPemeriksaan)
                                                            @if (($_item['status'] ?? '') === 'baik')
                                                                Installed
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik')
                                                                Not Installed
                                                            @else
                                                                -
                                                            @endif
                                                        @else
                                                            @if (($_item['status'] ?? '') === 'baik')
                                                                OK
                                                            @elseif(($_item['status'] ?? '') === 'tidak_baik')
                                                                NOT
                                                            @else
                                                                -
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs">
                                                        {{ $_item['keterangan'] ?? '' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3"
                                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-gray-400 text-center">
                                                        -</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    @if (!$_isPemeriksaan)
                                        <div
                                            class="mt-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-xs text-gray-500">
                                            <strong>Kondisi :</strong> <span class="mr-3">V : DONE</span> <span>X :
                                                NOT YET</span>
                                        </div>
                                        @if (!empty($_form['kondisi_akhir_notes']))
                                            <div class="mt-1 text-xs text-gray-500"><strong>Keterangan:</strong>
                                                {{ $_form['kondisi_akhir_notes'] }}</div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

                {{-- TINDAKAN (Pemeriksaan only) --}}
                @if ($_isPemeriksaan && !empty($_form['tindakan_categories']))
                    <div>
                        <div
                            class="text-xs font-bold mb-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 border-l-4 border-gray-500">
                            Tindakan</div>
                        <table class="w-full border border-gray-300 dark:border-gray-600">
                            @foreach ($_form['tindakan_categories'] as $_cat)
                                @if (!empty($_cat['selected'] ?? []))
                                    <tr>
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 font-semibold w-[35%] text-xs">
                                            {{ $_cat['label'] ?? '' }}</td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-xs">
                                            {{ implode(', ', $_cat['selected']) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            @if (!empty($_form['tindakan_solution']))
                                <tr>
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 font-semibold text-xs">
                                        Solution</td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-xs">
                                        {{ $_form['tindakan_solution'] }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                @endif

                {{-- KONDISI LEGEND (Pemeriksaan only) --}}
                @if ($_isPemeriksaan)
                    <div
                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-xs text-gray-500">
                        <strong>Kondisi :</strong> <span class="mr-3">V : BAIK</span> <span>X : TIDAK BAIK</span>
                        <span class="ml-2 text-[10px]">(Mohon jelaskan kerusakan atau masalah yang ada)</span>
                    </div>
                @endif

                {{-- CATATAN --}}
                <div class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs">
                    @if ($_isPemeriksaan)
                        <strong class="block mb-0.5">CATATAN
                            :</strong>{{ $_form['notes'] ?? '*) : diisi untuk perangkat lama' }}
                    @else
                        <strong class="block mb-0.5">Catatan Tambahan :</strong>
                        <p>Barcode Fisik : {{ !empty($_form['barcode_fisik']) ? 'Ada' : 'Tidak Ada' }}</p>
                        {{ $_form['notes'] ?? '-' }}
                    @endif
                </div>

                {{-- SIGNATURES --}}
                @if (!$_isPemeriksaan)
                    <div class="text-xs text-gray-500">Jakarta,
                        {{ $_form['submitted_at'] ? \Carbon\Carbon::parse($_form['submitted_at'])->format('d F Y') : '_______________' }}
                    </div>
                @endif
                <table class="w-full mt-4">
                    <tr class="text-center align-top">
                        <td class="w-1/3 px-1">
                            <div class="text-xs font-bold underline mb-1">{{ $_sigLabel1 }}</div>
                            @if ($_sigRole1)
                                <div class="text-[10px] text-gray-500 mb-2">{{ $_sigRole1 }}</div>
                            @endif
                            @if ($_diperiksa && !empty($_diperiksa['signature_path']))
                                <img src="{{ $_diperiksa['signature_path'] }}"
                                    class="w-[90px] h-[35px] mx-auto my-1 object-contain" alt="TTD">
                            @else<div class="w-[90px] border-b border-gray-400 mx-auto my-5"></div>
                            @endif
                            <div class="text-[10px] mt-1">{{ $_diperiksa['user']['name'] ?? '_______________' }}</div>
                            <div class="text-[9px] text-gray-500 mt-0.5">Tanggal :
                                {{ $_diperiksa && !empty($_diperiksa['approved_at']) ? \Carbon\Carbon::parse($_diperiksa['approved_at'])->format('d/m/Y') : '___/___/______' }}
                            </div>
                        </td>
                        <td class="w-1/3 px-1">
                            <div class="text-xs font-bold underline mb-1">{{ $_sigLabel2 }}</div>
                            @if ($_sigRole2)
                                <div class="text-[10px] text-gray-500 mb-2">{{ $_sigRole2 }}</div>
                            @endif
                            @if ($_diketahui && !empty($_diketahui['signature_path']))
                                <img src="{{ $_diketahui['signature_path'] }}"
                                    class="w-[90px] h-[35px] mx-auto my-1 object-contain" alt="TTD">
                            @else<div class="w-[90px] border-b border-gray-400 mx-auto my-5"></div>
                            @endif
                            <div class="text-[10px] mt-1">{{ $_diketahui['user']['name'] ?? '_______________' }}</div>
                            <div class="text-[9px] text-gray-500 mt-0.5">Tanggal :
                                {{ $_diketahui && !empty($_diketahui['approved_at']) ? \Carbon\Carbon::parse($_diketahui['approved_at'])->format('d/m/Y') : '___/___/______' }}
                            </div>
                        </td>
                        <td class="w-1/3 px-1">
                            <div class="text-xs font-bold underline mb-1">{{ $_sigLabel3 }}</div>
                            @if ($_sigRole3)
                                <div class="text-[10px] text-gray-500 mb-2">{{ $_sigRole3 }}</div>
                            @endif
                            @if ($_disetujui && !empty($_disetujui['signature_path']))
                                <img src="{{ $_disetujui['signature_path'] }}"
                                    class="w-[90px] h-[35px] mx-auto my-1 object-contain" alt="TTD">
                            @else<div class="w-[90px] border-b border-gray-400 mx-auto my-5"></div>
                            @endif
                            <div class="text-[10px] mt-1">{{ $_disetujui['user']['name'] ?? '_______________' }}</div>
                            <div class="text-[9px] text-gray-500 mt-0.5">Tanggal :
                                {{ $_disetujui && !empty($_disetujui['approved_at']) ? \Carbon\Carbon::parse($_disetujui['approved_at'])->format('d/m/Y') : '___/___/______' }}
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- FOOTER --}}
                <div class="text-center text-[9px] text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
                    @if ($_isPemeriksaan)
                        FM-ASRI/ITE/08-00 - Form Pemeriksaan Perangkat &mdash; {{ $_form['nomor_form'] }} &mdash;
                        {{ $_form['asset']['nama_perangkat'] ?? '' }}
                    @else
                        FM/ASRI/ITE/09-00 - Form Perawatan Perangkat &mdash; {{ $_form['nomor_form'] }} &mdash;
                        {{ $_form['asset']['nama_perangkat'] ?? '' }}
                    @endif
                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route($_type . '.export-pdf', $_form['id']) }}" target="_blank"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">Export
                        PDF</a>
                    @if (in_array($_form['status'], ['draft', 'submitted', 'diketahui']) &&
                            (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teknisi')))
                        <a href="{{ route($_type . '.create') }}?formId={{ $_form['id'] }}" wire:navigate
                            class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">Edit</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
</div>
