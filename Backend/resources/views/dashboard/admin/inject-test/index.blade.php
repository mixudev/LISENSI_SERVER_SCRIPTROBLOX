@extends('dashboard.admin.layouts.main')
@section('title', 'Test Inject')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Test Inject</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">Debug alur inject dari browser — tanpa perlu Roblox atau ngrok</p>
    </div>
    <a href="{{ route('admin.api-logs.index', ['endpoint' => 'inject']) }}"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Lihat Log API Inject
    </a>
</div>

{{-- Penjelasan --}}
<div class="mb-5 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/40">
    <div class="flex items-start gap-3">
        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-xs text-amber-800 dark:text-amber-300 space-y-1">
            <p class="font-bold">Kenapa Roblox tidak bisa akses localhost?</p>
            <p>Executor Roblox berjalan di cloud Roblox. Request <code class="bg-amber-100 dark:bg-amber-900/30 px-1">game:HttpGet("http://localhost:8000/...")</code> akan gagal karena <code>localhost</code> merujuk ke server Roblox, bukan komputer kamu.</p>
            <p class="mt-1"><strong>Untuk testing lokal:</strong> Gunakan tool ini (test dari browser). <strong>Untuk Roblox:</strong> Pakai <a href="https://ngrok.com" target="_blank" class="underline">ngrok</a> atau deploy ke server publik, lalu update <code>APP_URL</code> di <code>.env</code>.</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- FORM TEST --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Parameter Test</h3>
        </div>
        <div class="p-5 space-y-4">

            {{-- Quick pick license --}}
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">
                    Pilih License Aktif
                </label>
                <select id="quickPick" onchange="fillKey(this.value)"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500">
                    <option value="">— Pilih atau isi manual di bawah —</option>
                    @foreach ($licenses as $lic)
                    <option value="{{ $lic->license_key }}">
                        {{ $lic->license_key }} · {{ $lic->product?->name ?? '?' }}
                        @if ($lic->hwid) [HWID terikat] @endif
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">
                    License Key <span class="text-red-400">*</span>
                </label>
                <input id="inputKey" type="text" placeholder="LZD-XXXX-XXXX-XXXX-XXXX"
                    class="w-full px-3 py-2 text-sm font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">
                    HWID (simulasi device ID)
                </label>
                <input id="inputHwid" type="text" value="TEST-HWID-BROWSER-DEBUG"
                    class="w-full px-3 py-2 text-sm font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500">
                <p class="text-[10px] text-slate-400 mt-1">Kalau license sudah terikat HWID lain, test ini tetap jalan (simulasi skip mismatch).</p>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">
                    Place ID (opsional — untuk test deteksi map)
                </label>
                <input id="inputPlaceId" type="text" placeholder="0 = universal"
                    class="w-full px-3 py-2 text-sm font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500">
            </div>

            <button onclick="runTest()"
                class="w-full py-2.5 text-sm font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors flex items-center justify-center gap-2">
                <svg id="btnSpinner" class="w-4 h-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span id="btnLabel">▶ Jalankan Test Inject</span>
            </button>
        </div>

        {{-- Executor script box --}}
        <div id="executorBox" class="hidden border-t border-slate-100 dark:border-slate-800 p-5">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Script untuk Executor Roblox</p>
            <div class="relative bg-slate-900 dark:bg-slate-950 border border-slate-700">
                <button onclick="copyExecutorScript()"
                    class="absolute top-2 right-2 text-[10px] font-semibold text-slate-400 hover:text-white px-2 py-1 bg-slate-800 hover:bg-slate-700 transition-colors">
                    Salin
                </button>
                <pre id="executorScript" class="p-4 pr-16 text-xs font-mono text-slate-300 overflow-x-auto leading-relaxed whitespace-pre-wrap"></pre>
            </div>
            <p class="text-[10px] text-amber-500 dark:text-amber-400 mt-2">
                ⚠ URL ini pakai <strong>{{ config('app.url') }}</strong> — Roblox tidak bisa akses localhost.
                Ganti <code class="bg-slate-100 dark:bg-slate-800 px-1">APP_URL</code> di <code>.env</code> ke URL publik/ngrok.
            </p>
        </div>
    </div>

    {{-- HASIL TEST --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Hasil Step-by-Step</h3>
            <span id="resultBadge" class="hidden text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider"></span>
        </div>

        <div id="stepsContainer" class="p-5">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-8 h-8 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-slate-400">Isi form dan klik "Jalankan Test Inject"</p>
            </div>
        </div>
    </div>
</div>

{{-- URL checker --}}
<div class="mt-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Cek URL Endpoint</h3>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ([
                ['label' => 'Loader.lua', 'url' => url('/Loader.lua'), 'desc' => 'Di-download executor saat dijalankan'],
                ['label' => 'POST /api/license/inject', 'url' => url('/api/license/inject'), 'desc' => 'Endpoint validasi + ambil token'],
                ['label' => 'GET /modules/...', 'url' => url('/modules/config/settings.lua'), 'desc' => 'Serve modul Lua ke executor'],
            ] as $ep)
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                <p class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">{{ $ep['label'] }}</p>
                <code class="text-[9px] font-mono text-violet-600 dark:text-violet-400 break-all block mb-1">{{ $ep['url'] }}</code>
                <p class="text-[9px] text-slate-400">{{ $ep['desc'] }}</p>
                <a href="{{ $ep['url'] }}" target="_blank"
                    class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Buka
                </a>
            </div>
            @endforeach
        </div>
        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-700/40">
            <p class="text-xs font-bold text-blue-700 dark:text-blue-400 mb-1">APP_URL saat ini: <code>{{ config('app.url') }}</code></p>
            @if (str_contains(config('app.url'), 'localhost') || str_contains(config('app.url'), '127.0.0.1'))
            <p class="text-[11px] text-blue-600 dark:text-blue-400">
                ⚠ Ini URL lokal — Roblox <strong>tidak bisa</strong> akses. Untuk test Roblox:
                <br>1. Jalankan <code class="bg-blue-100 dark:bg-blue-900/30 px-1">ngrok http {{ parse_url(config('app.url'), PHP_URL_PORT) ?: 8000 }}</code>
                <br>2. Update <code>.env</code>: <code>APP_URL=https://xxxx.ngrok.io</code>
                <br>3. Restart server Laravel: <code>php artisan serve</code>
            </p>
            @else
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400">✓ URL publik terdeteksi — Roblox seharusnya bisa akses.</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function fillKey(val) {
    if (val) document.getElementById('inputKey').value = val;
}

function runTest() {
    var key     = document.getElementById('inputKey').value.trim();
    var hwid    = document.getElementById('inputHwid').value.trim();
    var placeId = document.getElementById('inputPlaceId').value.trim();

    if (!key) {
        alert('Masukkan license key terlebih dahulu.');
        return;
    }

    // Loading state
    document.getElementById('btnSpinner').classList.remove('hidden');
    document.getElementById('btnLabel').textContent = 'Testing...';
    document.getElementById('resultBadge').classList.add('hidden');
    document.getElementById('executorBox').classList.add('hidden');
    document.getElementById('stepsContainer').innerHTML = '<div class="py-8 text-center text-xs text-slate-400">Menjalankan test...</div>';

    fetch('{{ route('admin.inject-test.run') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ key: key, hwid: hwid, place_id: placeId })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        renderSteps(data);

        if (data.ok && data.get_url) {
            var key = document.getElementById('inputKey').value.trim();
            var base = data.get_url;
            var script = 'script_key = "' + key + '"\n'
                + 'loadstring(game:HttpGet("' + (data.loader_url || base.replace('/api/license/get', '/Loader.lua')) + '"))()';
            document.getElementById('executorScript').textContent = script;
            document.getElementById('executorBox').classList.remove('hidden');
        }
    })
    .catch(function(e) {
        document.getElementById('stepsContainer').innerHTML =
            '<div class="p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700/40 text-xs text-red-600 dark:text-red-400 font-mono">'
            + 'Fetch error: ' + e.message + '</div>';
    })
    .finally(function() {
        document.getElementById('btnSpinner').classList.add('hidden');
        document.getElementById('btnLabel').textContent = '▶ Jalankan Test Inject';
    });
}

function renderSteps(data) {
    var badge = document.getElementById('resultBadge');
    if (data.ok) {
        badge.textContent = '✓ Berhasil';
        badge.className = 'text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
    } else {
        badge.textContent = '✕ Gagal';
        badge.className = 'text-[10px] font-bold px-2 py-0.5 uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    }
    badge.classList.remove('hidden');

    var icons = { ok: '✓', fail: '✕', warn: '⚠' };
    var colors = {
        ok:   'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-700/40',
        fail: 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-700/40',
        warn: 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-700/40',
    };
    var textColors = {
        ok:   'text-emerald-700 dark:text-emerald-400',
        fail: 'text-red-700 dark:text-red-400',
        warn: 'text-amber-700 dark:text-amber-400',
    };

    var steps = data.steps || [];
    var html = '<div class="space-y-2">';
    steps.forEach(function(s, i) {
        var st = s.status || 'ok';
        html += '<div class="flex items-start gap-3 p-3 border ' + colors[st] + '">';
        html += '<span class="text-sm font-bold shrink-0 ' + textColors[st] + '">' + icons[st] + '</span>';
        html += '<div class="flex-1 min-w-0">';
        html += '<p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-0.5">' + escHtml(s.step) + '</p>';
        html += '<pre class="text-xs ' + textColors[st] + ' whitespace-pre-wrap font-mono">' + escHtml(s.detail || '') + '</pre>';
        html += '</div></div>';
    });

    if (data.ok && data.summary) {
        html += '<div class="mt-3 p-3 bg-violet-50 dark:bg-violet-900/10 border border-violet-200 dark:border-violet-700/40">';
        html += '<p class="text-[10px] font-bold uppercase tracking-widest text-violet-500 mb-2">Ringkasan</p>';
        var s = data.summary;
        html += '<div class="grid grid-cols-2 gap-1 text-[10px] font-mono">';
        html += '<span class="text-slate-400">Produk:</span><span class="text-slate-700 dark:text-slate-300">' + escHtml(s.product || '—') + '</span>';
        html += '<span class="text-slate-400">Script folder:</span><span class="text-slate-700 dark:text-slate-300">' + escHtml(s.folder) + '</span>';
        html += '<span class="text-slate-400">Script size:</span><span class="text-slate-700 dark:text-slate-300">' + escHtml(String(s.script_bytes)) + ' bytes</span>';
        html += '<span class="text-slate-400">Token:</span><span class="text-slate-700 dark:text-slate-300">' + escHtml(s.token) + '</span>';
        html += '</div></div>';
    }

    html += '</div>';
    document.getElementById('stepsContainer').innerHTML = html;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function copyExecutorScript() {
    var text = document.getElementById('executorScript').textContent;
    navigator.clipboard.writeText(text).then(function() {
        var btn = event.target;
        btn.textContent = '✓ Tersalin!';
        setTimeout(function() { btn.textContent = 'Salin'; }, 2000);
    });
}
</script>
@endpush

@endsection
