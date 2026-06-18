@extends('dashboard.user.layouts.main')
@section('title', 'Beranda')
@section('content')

{{-- ══ EXPIRY WARNING BANNER ══ --}}
@if ($expiringLicenses->isNotEmpty())
<div class="flex items-start gap-3 mb-5 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-300 dark:border-amber-700/50">
    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
        <p class="text-xs font-bold text-amber-800 dark:text-amber-300">
            {{ $expiringLicenses->count() }} lisensi akan expired dalam 7 hari
        </p>
        <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Segera hubungi admin untuk perpanjangan.</p>
    </div>
</div>
@endif

{{-- ══ PAGE HEADER ══ --}}
<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">
        Selamat datang, {{ auth()->user()->name }}
    </h2>
    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    @foreach ([
        ['label' => 'Lisensi Aktif',   'value' => $stats['active'],        'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Kadaluarsa',      'value' => $stats['expired'],       'color' => 'slate',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Segera Expired',  'value' => $stats['expiring_soon'], 'color' => 'amber',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ] as $card)
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $card['label'] }}</p>
            <svg class="w-4 h-4 text-{{ $card['color'] }}-500 dark:text-{{ $card['color'] }}-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $card['icon'] !!}</svg>
        </div>
        <p class="text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- ══ ACTIVE LICENSES ══ --}}
<div class="mb-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Lisensi Aktif</span>
        <a href="{{ route('user.licenses.index') }}"
            class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">
            Lihat semua →
        </a>
    </div>

    @forelse ($activeLicenses as $license)
    @php
        $lsv = $license->status instanceof \App\Enums\LicenseStatus ? $license->status->value : $license->status;
    @endphp
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 last:border-0">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                {{-- Product name + status --}}
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-700/40">
                        {{ $license->product?->name ?? 'Produk' }}
                    </span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40">
                        Aktif
                    </span>
                    @if ($license->hwid)
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                        Terikat HWID
                    </span>
                    @endif
                    @if ($license->roblox_username)
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-700/40">
                        Online: {{ $license->roblox_username }}
                    </span>
                    @endif
                </div>

                {{-- License key with copy button --}}
                <div class="flex items-center gap-2">
                    <code class="text-xs font-mono font-semibold text-slate-800 dark:text-slate-200 truncate">
                        {{ $license->license_key }}
                    </code>
                    <button onclick="copyText('{{ $license->license_key }}', this)"
                        class="shrink-0 text-slate-300 dark:text-slate-600 hover:text-violet-500 dark:hover:text-violet-400 transition-colors" title="Salin key">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>

                {{-- Expiry --}}
                <p class="text-[10px] text-slate-400 mt-1">
                    @if ($license->expired_at)
                        Expired: <span class="{{ $license->expired_at->diffInDays() <= 7 ? 'text-amber-600 dark:text-amber-400 font-semibold' : '' }}">{{ $license->expired_at->format('d M Y') }}</span>
                        <span class="text-slate-300 dark:text-slate-600">·</span>
                        {{ $license->expired_at->diffForHumans() }}
                    @else
                        <span class="text-violet-600 dark:text-violet-400 font-semibold">Lifetime</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    @empty
    <div class="px-5 py-12 text-center">
        <svg class="w-8 h-8 text-slate-300 dark:text-slate-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
        <p class="text-sm font-semibold text-slate-400">Belum ada lisensi aktif</p>
        <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Hubungi admin untuk mendapatkan lisensi.</p>
    </div>
    @endforelse
</div>

{{-- ══ PANDUAN PENGGUNAAN ROBLOX EXECUTOR ══ --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">

    {{-- Header --}}
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
        <div class="w-6 h-6 bg-violet-600 flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Cara Pakai di Roblox Executor</span>
    </div>

    <div class="p-5 space-y-6">

        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Script diproteksi dengan sistem lisensi HWID. Hanya satu perangkat yang bisa menggunakan satu key.
            Gunakan format berikut di executor Roblox kamu.
        </p>

        {{-- Format Executor --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-[10px] font-bold text-white bg-violet-600 px-2 py-0.5">FORMAT</span>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Script untuk Executor Roblox</p>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                Tempel kode ini ke executor kamu, ganti <code class="bg-slate-100 dark:bg-slate-800 px-1 text-violet-600 dark:text-violet-400">YOUR_LICENSE_KEY</code> dengan key milikmu.
            </p>

            <div>
                <div class="relative bg-slate-900 dark:bg-slate-950 border border-emerald-800/40">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-slate-700 dark:border-slate-800">
                        <span class="text-[10px] font-mono text-emerald-400 uppercase tracking-widest">script_key + Loader.lua</span>
                        <button onclick="copyCode('code-executor', this)"
                            class="text-[10px] font-semibold text-slate-400 hover:text-white flex items-center gap-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Salin
                        </button>
                    </div>
                    <pre id="code-executor" class="p-4 text-xs font-mono text-slate-300 overflow-x-auto leading-relaxed"><span class="text-sky-400">script_key</span> = <span class="text-green-400">"YOUR_LICENSE_KEY"</span>
<span class="text-sky-400">loadstring</span>(<span class="text-sky-400">game</span>:<span class="text-amber-300">HttpGet</span>(<span class="text-green-400">"{{ rtrim(config('app.url'), '/') }}/Loader.lua"</span>))()</pre>
                </div>
            </div>
        </div>

        {{-- Contoh dengan key nyata (jika user punya lisensi aktif) --}}
        @if ($activeLicenses->isNotEmpty())
        @php $firstActive = $activeLicenses->first(); @endphp
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-[10px] font-bold text-white bg-emerald-600 px-2 py-0.5">KEY KAMU</span>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Siap pakai — salin dan paste ke executor</p>
            </div>
            <div class="relative bg-slate-900 dark:bg-slate-950 border border-emerald-800/40 dark:border-emerald-700/30">
                <div class="flex items-center justify-between px-4 py-2 border-b border-emerald-800/40 dark:border-emerald-700/30">
                    <span class="text-[10px] font-mono text-emerald-400 uppercase tracking-widest">{{ $firstActive->product?->name ?? 'Produk' }} · Ready to Use</span>
                    <button onclick="copyCode('code-ready', this)"
                        class="text-[10px] font-semibold text-slate-400 hover:text-emerald-400 flex items-center gap-1 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Salin
                    </button>
                </div>
                <pre id="code-ready" class="p-4 text-xs font-mono text-slate-300 overflow-x-auto leading-relaxed"><span class="text-sky-400">script_key</span> = <span class="text-green-400">"{{ $firstActive->license_key }}"</span>
<span class="text-sky-400">loadstring</span>(<span class="text-sky-400">game</span>:<span class="text-amber-300">HttpGet</span>(<span class="text-green-400">"{{ rtrim(config('app.url'), '/') }}/Loader.lua"</span>))()</pre>
            </div>
        </div>
        @endif

        {{-- Cara kerja sistem --}}
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="text-[10px] font-bold text-white bg-slate-600 dark:bg-slate-700 px-2 py-0.5">CARA KERJA</span>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Proses Validasi Otomatis</p>
            </div>
            <div class="space-y-2">
                @foreach ([
                    ['num' => '1', 'color' => 'violet', 'title' => 'Key Dikirim', 'desc' => 'Executor mengirim script_key ke server kami saat script dijalankan.'],
                    ['num' => '2', 'color' => 'blue',   'title' => 'HWID Diverifikasi', 'desc' => 'Server mengecek apakah perangkat kamu sudah terdaftar. Pertama kali, HWID otomatis terikat.'],
                    ['num' => '3', 'color' => 'emerald','title' => 'Script Terenkripsi Diterima', 'desc' => 'Jika valid, server mengirim script dalam format terenkripsi. Otomatis terdeteksi map yang sedang dimainkan.'],
                    ['num' => '4', 'color' => 'amber',  'title' => 'Inject & Jalankan', 'desc' => 'Script didekripsi dan dijalankan di game. Jika kamu pindah map, script menyesuaikan otomatis.'],
                ] as $step)
                <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                    <span class="w-5 h-5 flex items-center justify-center bg-{{ $step['color'] }}-600 text-white text-[10px] font-bold shrink-0">{{ $step['num'] }}</span>
                    <div>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $step['title'] }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $step['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Error codes --}}
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Pesan Error & Penanganannya</p>
            <div class="border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ([
                    ['code' => 'HWID_MISMATCH',    'color' => 'red',    'msg' => 'Kamu menggunakan key ini dari perangkat berbeda. Reset HWID dari halaman Lisensi.'],
                    ['code' => 'LICENSE_NOT_FOUND', 'color' => 'slate',  'msg' => 'Key tidak ditemukan. Pastikan tidak ada typo di script_key.'],
                    ['code' => 'LICENSE_EXPIRED',   'color' => 'amber',  'msg' => 'Masa aktif lisensi habis. Hubungi admin untuk perpanjangan.'],
                    ['code' => 'LICENSE_BANNED',    'color' => 'red',    'msg' => 'Lisensi diblokir oleh admin. Hubungi admin untuk informasi lebih lanjut.'],
                ] as $err)
                <div class="flex items-start gap-3 px-4 py-3">
                    <code class="text-[10px] font-mono font-bold text-{{ $err['color'] }}-600 dark:text-{{ $err['color'] }}-400 shrink-0 mt-0.5">{{ $err['code'] }}</code>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $err['msg'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function copyCode(id, btn) {
    var pre = document.getElementById(id);
    if (!pre) return;
    var text = pre.innerText;
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-3 h-3 text-emerald-400 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Tersalin!';
        btn.classList.add('text-emerald-400');
        setTimeout(function() {
            btn.innerHTML = orig;
            btn.classList.remove('text-emerald-400');
        }, 2000);
    });
}
</script>
@endpush

@endsection
