@extends('dashboard.admin.layouts.main')
@section('title', 'Detail Lisensi')
@section('content')

@php
    $licenseType = $license->license_type ?? 'user';
    $typeBadge = $licenseType === 'admin'
        ? 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-700/40'
        : 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/20 dark:text-sky-400 dark:border-sky-700/40';
    $statusBadge = match($license->status) {
        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40',
        'expired'   => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
        'banned'    => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-700/40',
        'suspended' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-700/40',
        default     => 'bg-slate-50 text-slate-500 border-slate-200',
    };
    $statusDot = match($license->status) {
        'active'    => 'bg-emerald-500',
        'expired'   => 'bg-slate-400',
        'banned'    => 'bg-red-500',
        'suspended' => 'bg-amber-500',
        default     => 'bg-slate-400',
    };
@endphp

{{-- ── BREADCRUMB + HEADER ── --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('admin.licenses.index') }}"
            class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors font-medium">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Lisensi
        </a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <code class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $license->license_key }}</code>
        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $typeBadge }}">
            {{ $licenseType === 'admin' ? 'Admin' : 'User' }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $statusBadge }}">
            <span class="w-1.5 h-1.5 {{ $statusDot }}"></span>
            {{ $license->status }}
        </span>
    </div>

    <div class="flex items-center gap-2">
        <button onclick="AppModal.open('modalEdit')"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </button>
        <button onclick="triggerResetHwid()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reset HWID
        </button>
        <button onclick="triggerDelete()"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Hapus
        </button>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- ══ LEFT: Info + HWID ══ --}}
    <div class="xl:col-span-2 flex flex-col gap-5">

        {{-- License Overview --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">License Key</p>
                        <code class="text-sm font-mono font-bold text-slate-900 dark:text-white tracking-widest">{{ $license->license_key }}</code>
                    </div>
                </div>
                <button onclick="navigator.clipboard.writeText('{{ $license->license_key }}').then(()=>AppPopup.success({title:'Tersalin',description:'License key disalin.'}))"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-violet-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Salin
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y divide-slate-100 dark:divide-slate-800">
                @php
                    $metaItems = [
                        ['label' => 'Tipe Lisensi',  'value' => $licenseType === 'admin' ? 'Admin' : 'User', 'mono' => false],
                        ['label' => 'Pemilik',        'value' => $license->user?->name ?? 'Belum di-assign', 'mono' => false],
                        ['label' => 'Email Pemilik',  'value' => $license->user?->email ?? '—', 'mono' => false],
                        ['label' => 'Discord ID',     'value' => $license->discord_id ?? '—', 'mono' => true],
                        ['label' => 'Dibuat oleh',    'value' => $license->creator?->name ?? 'System', 'mono' => false],
                        ['label' => 'Diaktifkan',     'value' => $license->activated_at?->format('d M Y, H:i') ?? 'Belum aktivasi', 'mono' => false],
                        ['label' => 'Expired',        'value' => $license->expired_at?->format('d M Y, H:i') ?? 'Lifetime', 'mono' => false],
                        ['label' => 'Terakhir Pakai', 'value' => $license->last_used_at?->diffForHumans() ?? '—', 'mono' => false],
                        ['label' => 'Reset HWID',     'value' => $license->hwid_reset_count.'x', 'mono' => true],
                        ['label' => 'Roblox User',    'value' => $license->roblox_username ?? '—', 'mono' => false],
                        ['label' => 'Map Terakhir',   'value' => $license->roblox_place_id ? \App\Services\ScriptService::getMapNameFromPlaceId($license->roblox_place_id) : '—', 'mono' => false],
                        ['label' => 'IP Terakhir',    'value' => $license->last_ip ?? '—', 'mono' => true],
                        ['label' => 'HWID Reset',     'value' => $license->hwid_last_reset_at?->diffForHumans() ?? 'Belum pernah', 'mono' => false],
                    ];
                @endphp
                @foreach ($metaItems as $item)
                <div class="px-5 py-4">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">{{ $item['label'] }}</p>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 {{ $item['mono'] ? 'font-mono text-xs' : '' }}">{{ $item['value'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Script access --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-3">Produk Script yang Bisa Diakses</p>
                @if ($accessibleProducts->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($accessibleProducts as $product)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold border
                        {{ $product->access_level === 'admin' ? 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-700/40 dark:bg-violet-900/20 dark:text-violet-400' : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-700/40 dark:bg-emerald-900/20 dark:text-emerald-400' }}">
                        {{ $product->name }}
                        <span class="font-mono font-normal opacity-70">· {{ $product->script_folder ?? 'github' }}</span>
                    </span>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-amber-600 dark:text-amber-400">Tidak ada produk aktif yang cocok untuk tipe lisensi ini.</p>
                @endif
            </div>

            {{-- HWID section --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">HWID Terikat</p>
                @if ($license->hwid)
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        <div class="w-2 h-2 bg-emerald-500 shrink-0"></div>
                        <code class="text-xs font-mono text-slate-700 dark:text-slate-300 break-all flex-1">{{ $license->hwid }}</code>
                    </div>
                @else
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-dashed border-slate-200 dark:border-slate-700">
                        <div class="w-2 h-2 bg-slate-300 dark:bg-slate-600 shrink-0"></div>
                        <p class="text-xs text-slate-400 italic">Belum ada HWID — akan terikat saat pertama kali diaktifkan</p>
                    </div>
                @endif
            </div>

            @if ($license->ban_reason)
            <div class="px-6 py-4 border-t border-red-100 dark:border-red-900/30 bg-red-50/50 dark:bg-red-900/10">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-red-500 mb-1">Alasan Ban / Suspend</p>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $license->ban_reason }}</p>
            </div>
            @endif

            @if ($license->notes)
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Catatan Internal</p>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $license->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Activity Log --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Aktivitas Lisensi</h3>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @forelse ($license->activities->take(8) as $act)
                <div class="px-6 py-3 flex items-center gap-4">
                    <div class="w-1.5 h-1.5 bg-violet-400 shrink-0"></div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-violet-600 dark:text-violet-400 w-32 shrink-0">{{ str_replace('_', ' ', $act->action) }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 flex-1">{{ $act->user?->name ?? 'System' }}</span>
                    <span class="text-[10px] font-mono text-slate-400">{{ $act->created_at->format('d/m H:i') }}</span>
                </div>
                @empty
                <p class="px-6 py-8 text-xs text-slate-400 text-center">Belum ada aktivitas tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══ RIGHT: HWID Reset Log + API ══ --}}
    <div class="flex flex-col gap-5">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Riwayat Reset HWID</h3>
                <span class="text-[10px] font-mono text-slate-400">{{ $license->hwidResetLogs->count() }} reset</span>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @forelse ($license->hwidResetLogs as $log)
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $log->reset_by === 'admin' ? 'text-violet-600 dark:text-violet-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $log->reset_by === 'admin' ? 'Admin' : 'User' }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if ($log->old_hwid)
                    <div class="flex items-center gap-2 text-[10px]">
                        <span class="text-slate-400 w-8">Lama</span>
                        <code class="font-mono text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 px-1.5 py-0.5 truncate max-w-[120px]">{{ substr($log->old_hwid, 0, 12) }}…</code>
                    </div>
                    @endif
                    @if ($log->reason)
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1.5 italic">{{ $log->reason }}</p>
                    @endif
                    @if ($log->ip)
                    <p class="text-[10px] font-mono text-slate-400 mt-1">{{ $log->ip }}</p>
                    @endif
                </div>
                @empty
                <div class="px-5 py-10 text-center">
                    <p class="text-xs text-slate-400">Belum ada reset HWID</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Log API Terakhir</h3>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @forelse ($usageLogs->take(10) as $log)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="flex-1 min-w-0">
                        @if ($log->roblox_username || $log->roblox_place_id)
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
                                {{ $log->roblox_username ?? '—' }}
                            </p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                {{ $log->roblox_place_id
                                    ? \App\Services\ScriptService::getMapNameFromPlaceId($log->roblox_place_id)
                                    : 'Map tidak diketahui' }}
                            </p>
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                {{ str_contains($log->endpoint, 'activate') ? 'Aktivasi' : (str_contains($log->endpoint, 'check') ? 'Cek lisensi' : $log->endpoint) }}
                            </p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] font-mono text-slate-400">{{ $log->created_at->format('d/m H:i') }}</span>
                        <p class="text-[9px] font-bold uppercase tracking-wider mt-0.5 {{ $log->http_code < 300 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                            {{ $log->status }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-xs text-slate-400 text-center">Belum ada request API.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<x-allert.app-modal id="modalEdit" maxWidth="lg" title="Edit Lisensi" description="Perubahan akan langsung diterapkan"
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>
    <form id="formEdit" method="POST" action="{{ route('admin.licenses.update', $license) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="modal-label">Tipe Lisensi</label>
                <select name="license_type" class="modal-input modal-select">
                    <option value="user" @selected($licenseType === 'user')>User</option>
                    <option value="admin" @selected($licenseType === 'admin')>Admin</option>
                </select>
            </div>
            <div>
                <label class="modal-label">Status</label>
                <select name="status" class="modal-input modal-select">
                    @foreach (['active' => 'Aktif', 'suspended' => 'Suspended', 'banned' => 'Banned'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($license->status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="modal-label">Expired At</label>
                <input type="datetime-local" name="expired_at" class="modal-input"
                    value="{{ $license->expired_at?->format('Y-m-d\TH:i') }}">
            </div>
            <div>
                <label class="modal-label">Assign User (ID)</label>
                <input type="number" name="user_id" class="modal-input" value="{{ $license->user_id }}" placeholder="ID user">
            </div>
            <div>
                <label class="modal-label">Discord ID</label>
                <input type="text" name="discord_id" class="modal-input" value="{{ $license->discord_id }}" placeholder="Discord User ID">
            </div>
            <div class="col-span-2">
                <label class="modal-label">Alasan Ban / Suspend</label>
                <input type="text" name="ban_reason" class="modal-input" value="{{ $license->ban_reason }}" placeholder="Isi jika status banned/suspended">
            </div>
            <div class="col-span-2">
                <label class="modal-label">Catatan Internal</label>
                <textarea name="notes" rows="2" class="modal-input" placeholder="Opsional...">{{ $license->notes }}</textarea>
            </div>
        </div>
    </form>
    <x-slot name="footer">
        <button onclick="AppModal.close('modalEdit')" class="modal-btn-cancel">Batal</button>
        <button onclick="document.getElementById('formEdit').submit()" class="modal-btn-primary">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Simpan Perubahan
        </button>
    </x-slot>
</x-allert.app-modal>

<form id="formResetHwid" method="POST" action="{{ route('admin.licenses.reset-hwid', $license) }}" class="hidden">@csrf</form>
<form id="formDelete" method="POST" action="{{ route('admin.licenses.destroy', $license) }}" class="hidden">@csrf @method('DELETE')</form>

@push('scripts')
<script>
function triggerResetHwid() {
    AppPopup.confirm({
        title: 'Reset HWID?',
        description: 'Lisensi <strong class="font-mono text-xs">{{ $license->license_key }}</strong> akan terlepas dari perangkat saat ini.',
        confirmText: 'Ya, Reset HWID',
        cancelText: 'Batal',
        onConfirm: () => document.getElementById('formResetHwid').submit(),
    });
}

function triggerDelete() {
    AppPopup.confirm({
        title: 'Hapus Lisensi?',
        description: 'Lisensi ini akan dihapus <strong>permanen</strong> beserta semua data terkait.',
        confirmText: 'Ya, Hapus Permanen',
        cancelText: 'Batal',
        onConfirm: () => document.getElementById('formDelete').submit(),
    });
}
</script>
@endpush

@endsection
