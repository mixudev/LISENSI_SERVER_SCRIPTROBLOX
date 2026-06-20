@extends('dashboard.user.layouts.main')
@section('title', 'Lisensi Saya')
@section('content')

{{-- Filter --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach (['all' => 'Semua', 'active' => 'Aktif', 'expired' => 'Kadaluarsa'] as $val => $label)
        <a href="{{ route('user.licenses.index', ['filter' => $val]) }}"
            class="text-[10px] font-bold uppercase tracking-wider px-4 py-2 border transition-colors
                {{ $filter === $val
                    ? 'bg-violet-600 text-white border-violet-600'
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-violet-400' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- License Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse ($filtered as $license)
        @php
            $isExpiringSoon = $license->expired_at?->between(now(), now()->addDays(7));
            $isExpired = $license->isExpired() || $license->status === 'expired';
            $statusLabel = match($license->status) {
                'active'    => $isExpired ? 'Kadaluarsa' : 'Aktif',
                'expired'   => 'Kadaluarsa',
                'banned'    => 'Dibanned',
                'suspended' => 'Disuspend',
                default     => ucfirst($license->status),
            };
            $statusClasses = match(true) {
                $license->status === 'banned' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
                $license->status === 'suspended' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800',
                $isExpired => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                default => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800',
            };
        @endphp

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col">
            {{-- Header --}}
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-700/40 truncate">
                    {{ $license->product?->name ?? 'Produk' }}
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border {{ $statusClasses }} shrink-0">
                    {{ $statusLabel }}
                </span>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 flex-1 flex flex-col gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">License Key</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs font-mono font-semibold text-slate-800 dark:text-slate-200 flex-1 truncate">{{ $license->license_key }}</code>
                        <button onclick="copyText('{{ $license->license_key }}', this)"
                            class="shrink-0 text-slate-300 dark:text-slate-600 hover:text-violet-500 transition-colors" title="Salin">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">HWID</p>
                    <p class="text-xs font-mono text-slate-600 dark:text-slate-400">
                        {{ $license->hwid ? substr($license->hwid, 0, 16).'...' : 'Belum terikat' }}
                    </p>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Masa Aktif</p>
                    <p class="text-sm font-semibold {{ $isExpiringSoon ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-200' }}">
                        {{ $license->expired_at ? $license->expired_at->format('d M Y') : 'Seumur Hidup' }}
                        @if ($isExpiringSoon)
                            <span class="text-xs font-normal">({{ $license->expired_at->diffForHumans() }})</span>
                        @endif
                    </p>
                </div>

                <p class="text-[10px] text-slate-400">
                    Terakhir digunakan: {{ $license->last_used_at?->diffForHumans() ?? '—' }}
                    @if ($license->hwid_reset_count > 0)
                        · Reset HWID: {{ $license->hwid_reset_count }}x
                    @endif
                </p>
            </div>

            {{-- Footer Actions --}}
            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-2">
                @if ($license->isActive() && ! $isExpired)
                    @if ($license->canResetHwid())
                        <button type="button" onclick="openResetModal('reset-modal-{{ $license->id }}')"
                            class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-colors">
                            Reset HWID
                        </button>
                    @endif

                    @if ($license->product?->hasScript())
                        <a href="{{ route('user.licenses.download', $license) }}"
                            class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 hover:bg-violet-200 dark:hover:bg-violet-900/50 transition-colors">
                            Download Script
                        </a>
                    @endif
                @endif

                @if ($isExpiringSoon || $isExpired || in_array($license->status, ['expired', 'suspended']))
                    @include('dashboard.user.partials.contact-extend', [
                        'productName' => $license->product?->name,
                        'licenseKey' => $license->license_key,
                    ])
                @endif
            </div>
        </div>

        {{-- Reset HWID Modal --}}
        @if ($license->isActive() && ! $isExpired && $license->canResetHwid())
        <div id="reset-modal-{{ $license->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 w-full max-w-sm p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Konfirmasi Reset HWID</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    Setelah reset, perangkat lama tidak bisa digunakan sampai HWID baru terikat di perangkat baru.
                </p>
                <div class="flex gap-3">
                    <form method="POST" action="{{ route('user.licenses.reset-hwid', $license) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-orange-600 text-white text-xs font-bold uppercase tracking-wider hover:bg-orange-700 transition-colors">
                            Ya, Reset
                        </button>
                    </form>
                    <button type="button" onclick="closeResetModal('reset-modal-{{ $license->id }}')"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-wider hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
        @endif
    @empty
        <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <p class="text-sm font-semibold text-slate-400 mb-2">Tidak ada lisensi ditemukan.</p>
            <p class="text-xs text-slate-400 mb-4">Hubungi admin untuk mendapatkan lisensi.</p>
            @include('dashboard.user.partials.contact-extend', ['class' => 'justify-center'])
        </div>
    @endforelse
</div>

@push('scripts')
<script>
function openResetModal(id) {
    document.getElementById(id)?.classList.remove('hidden');
}
function closeResetModal(id) {
    document.getElementById(id)?.classList.add('hidden');
}
document.querySelectorAll('[id^="reset-modal-"]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === el) closeResetModal(el.id);
    });
});
</script>
@endpush

@endsection
