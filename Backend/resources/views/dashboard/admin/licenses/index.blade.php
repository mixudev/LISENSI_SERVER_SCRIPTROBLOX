@extends('dashboard.admin.layouts.main')
@section('title', 'Manajemen Lisensi')
@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Lisensi</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">Kelola seluruh license key produk</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.licenses.export', request()->query()) }}"
            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export CSV
        </a>
        <button onclick="AppModal.open('modalGenerateBulk')"
            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Generate Bulk
        </button>
        <button onclick="AppModal.open('modalGenerate')"
            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Generate Lisensi
        </button>
    </div>
</div>

{{-- ── FILTER BAR ── --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Cari Key / Email</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="LZD-XXXX atau email..."
                class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
        </div>
    </div>
    <div class="min-w-[140px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Status</label>
        <select name="status" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            <option value="">Semua Status</option>
            @foreach (['active' => 'Aktif', 'expired' => 'Kadaluarsa', 'banned' => 'Banned', 'suspended' => 'Suspended'] as $val => $lbl)
                <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-[160px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Produk</label>
        <select name="product_id" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            <option value="">Semua Produk</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">
            Filter
        </button>
        <a href="{{ route('admin.licenses.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- ── TABLE ── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">

    {{-- Table count bar --}}
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
            @if($licenses->total() > 0)
                Menampilkan <strong class="text-slate-700 dark:text-slate-200">{{ $licenses->firstItem() }}–{{ $licenses->lastItem() }}</strong> dari <strong class="text-slate-700 dark:text-slate-200">{{ number_format($licenses->total()) }}</strong> lisensi
            @else
                0 lisensi ditemukan
            @endif
        </span>
        <div class="flex items-center gap-1">
            @foreach (['active' => ['Aktif','emerald'], 'expired' => ['Expired','slate'], 'banned' => ['Banned','red'], 'suspended' => ['Suspended','amber']] as $s => [$lbl, $col])
                <a href="{{ route('admin.licenses.index', array_merge(request()->query(), ['status' => $s])) }}"
                    class="px-2 py-1 text-[10px] font-semibold border transition-colors
                        {{ ($filters['status'] ?? '') === $s
                            ? "bg-{$col}-600 text-white border-{$col}-700"
                            : 'text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">License Key</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Pemilik</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Tipe</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Roblox / Map</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Expired</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($licenses as $license)
                @php
                    $badge = match($license->status) {
                        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40',
                        'expired'   => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                        'banned'    => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-700/40',
                        'suspended' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-700/40',
                        default     => 'bg-slate-50 text-slate-500 border-slate-200',
                    };
                    $isExpired = $license->isExpired();
                @endphp
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors group">
                    {{-- License Key --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <code class="font-mono text-[11px] font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-1 tracking-wider">{{ $license->license_key }}</code>
                            <button
                                onclick="navigator.clipboard.writeText('{{ $license->license_key }}').then(()=>AppPopup.success({title:'Tersalin',description:'License key disalin ke clipboard.'}))"
                                class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-all" title="Salin">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                    {{-- Pemilik --}}
                    <td class="px-5 py-3.5">
                        @if ($license->user)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[9px] font-bold shrink-0">
                                    {{ strtoupper(substr($license->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-none">{{ $license->user->name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $license->user->email }}</p>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-slate-300 dark:text-slate-600 italic">Unassigned</span>
                        @endif
                    </td>
                    {{-- Tipe Lisensi --}}
                    <td class="px-5 py-3.5">
                        @php $lt = $license->license_type ?? 'user'; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border
                            {{ $lt === 'admin' ? 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-700/40 dark:bg-violet-900/20 dark:text-violet-400' : 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-700/40 dark:bg-sky-900/20 dark:text-sky-400' }}">
                            {{ $lt === 'admin' ? 'Admin' : 'User' }}
                        </span>
                    </td>
                    {{-- Roblox / Map --}}
                    <td class="px-5 py-3.5">
                        @if ($license->roblox_username)
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $license->roblox_username }}</p>
                                @if ($license->roblox_place_id)
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ \App\Services\ScriptService::getMapNameFromPlaceId($license->roblox_place_id) }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-slate-300 dark:text-slate-600 italic">—</span>
                        @endif
                    </td>
                    {{-- Expired --}}
                    <td class="px-5 py-3.5">
                        @if ($license->expired_at)
                            <span class="text-xs {{ $isExpired ? 'text-red-500 dark:text-red-400 font-semibold' : 'text-slate-600 dark:text-slate-400' }}">
                                {{ $license->expired_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 uppercase tracking-wider">Lifetime</span>
                        @endif
                    </td>
                    {{-- Aksi --}}
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1  transition-opacity">
                            <a href="{{ route('admin.licenses.show', $license) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-700/40 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                                title="Detail">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </a>
                            <button
                                onclick="confirmResetHwid('{{ $license->id }}', '{{ $license->license_key }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-700/40 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                title="Reset HWID">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset
                            </button>
                            <button
                                onclick="confirmDelete('{{ $license->id }}', '{{ $license->license_key }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700/40 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                title="Hapus">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-400">Tidak ada lisensi ditemukan</p>
                            <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Coba ubah filter atau generate lisensi baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($licenses->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <p class="text-xs text-slate-400 font-mono">
            Hal. {{ $licenses->currentPage() }} / {{ $licenses->lastPage() }}
        </p>
        <div class="text-xs">{{ $licenses->withQueryString()->links() }}</div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════ --}}
@include('dashboard.admin.licenses.modal')

{{-- Hidden forms for confirm actions --}}
<form id="formResetHwid" method="POST" class="hidden">
    @csrf
</form>
<form id="formDelete" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function confirmResetHwid(licenseId, licenseKey) {
    AppPopup.warning({
        title: 'Reset HWID?',
        description: `Lisensi <strong class="font-mono">${licenseKey}</strong> akan terlepas dari perangkat saat ini. User perlu bind ulang.`,
        confirmText: 'Ya, Reset HWID',
        cancelText: 'Batal',
        onConfirm: () => {
            const form = document.getElementById('formResetHwid');
            form.action = `/admin/licenses/${licenseId}/reset-hwid`;
            form.submit();
        }
    });
}

function confirmDelete(licenseId, licenseKey) {
    AppPopup.confirm({
        title: 'Hapus Lisensi?',
        description: `<code class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5">${licenseKey}</code> akan dihapus permanen dan tidak dapat dikembalikan.`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        onConfirm: () => {
            const form = document.getElementById('formDelete');
            form.action = `/admin/licenses/${licenseId}`;
            form.submit();
        }
    });
}
</script>
@endpush

@endsection
