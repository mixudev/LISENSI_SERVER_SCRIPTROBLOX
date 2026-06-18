@extends('dashboard.admin.layouts.main')
@section('title', 'Log API')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Log API</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">Monitoring seluruh request masuk — termasuk inject executor</p>
    </div>
    <button onclick="location.reload()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </button>
</div>

{{-- SUMMARY CARDS --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
    @foreach ([
        ['label' => 'Request Hari Ini', 'key' => 'total_today',   'color' => 'slate',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
        ['label' => 'Sukses',           'key' => 'success_today', 'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Gagal',            'key' => 'failed_today',  'color' => 'red',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Avg Response (ms)','key' => 'avg_response',  'color' => 'violet',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Inject Hari Ini',  'key' => 'inject_today',  'color' => 'rose',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ] as $card)
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ number_format($summary[$card['key']] ?? 0) }}</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 dark:text-slate-700 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $card['icon'] !!}</svg>
        </div>
    </div>
    @endforeach
</div>

{{-- FILTER BAR --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    @php $inp = 'px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500'; @endphp
    <div class="flex-1 min-w-[160px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Endpoint</label>
        <input type="text" name="endpoint" value="{{ request('endpoint') }}" placeholder="inject, activate..." class="w-full {{ $inp }}">
    </div>
    <div class="min-w-[130px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Status</label>
        <select name="status" class="w-full {{ $inp }}">
            <option value="">Semua</option>
            @foreach (['inject_success','success','LicenseNotFoundException','HwidMismatchException','LicenseBannedException','LicenseExpiredException'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-[130px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Produk</label>
        <input type="text" name="product_name" value="{{ request('product_name') }}" placeholder="Nama produk..." class="w-full {{ $inp }}">
    </div>
    <div class="min-w-[130px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Roblox Username</label>
        <input type="text" name="roblox_username" value="{{ request('roblox_username') }}" placeholder="PlayerName..." class="w-full {{ $inp }}">
    </div>
    <div class="min-w-[110px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">IP</label>
        <input type="text" name="ip" value="{{ request('ip') }}" placeholder="127.0.0.1" class="w-full {{ $inp }}">
    </div>
    <div>
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Dari</label>
        <input type="date" name="from" value="{{ request('from') }}" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Sampai</label>
        <input type="date" name="to" value="{{ request('to') }}" class="{{ $inp }}">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">Filter</button>
        <a href="{{ route('admin.api-logs.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Reset</a>
    </div>
</form>

{{-- TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-6 flex-wrap">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
            @if($logs->total() > 0)
                <strong class="text-slate-700 dark:text-slate-200">{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</strong>
                dari <strong class="text-slate-700 dark:text-slate-200">{{ number_format($logs->total()) }}</strong> log
            @else
                0 log ditemukan
            @endif
        </span>
        <div class="flex items-center gap-4 text-[10px] text-slate-400 ml-auto">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 bg-red-200 dark:bg-red-900/40 border border-red-300 dark:border-red-700/60 inline-block"></span> Error HTTP ≥ 400
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 bg-amber-100 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 inline-block"></span> Lambat &gt;200ms
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 bg-rose-100 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-700/40 inline-block"></span> Inject
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 w-[120px]">Waktu</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Endpoint</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">IP</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">License Key</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Produk</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Roblox</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Step</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">HTTP</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">ms</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($logs as $log)
                @php
                    $http     = $log->http_code ?? 200;
                    $ms       = $log->response_time_ms ?? 0;
                    $isInject = str_contains($log->endpoint ?? '', 'inject');
                    $hasError = $http >= 400;
                    $isSlow   = $ms > 200 && ! $hasError;

                    $rowBg = match(true) {
                        $http >= 500 => 'bg-red-50 dark:bg-red-900/10',
                        $http >= 400 => 'bg-orange-50/60 dark:bg-orange-900/5',
                        $isSlow      => 'bg-amber-50/50 dark:bg-amber-900/5',
                        $isInject    => 'bg-rose-50/30 dark:bg-rose-900/5',
                        default      => '',
                    };

                    $stepColors = [
                        'script_served' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                        'token_created' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        'get_script'    => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                        'done'          => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'license_check' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    ];
                    $stepColor = $stepColors[$log->inject_step ?? ''] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
                @endphp
                <tr class="{{ $rowBg }} hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-4 py-2.5">
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $log->created_at->format('d/m H:i:s') }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="text-[10px] font-mono {{ $isInject ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $log->endpoint }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400">{{ $log->ip }}</span>
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($log->license_key_used)
                            <code class="text-[10px] font-mono text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 px-1 py-0.5 tracking-wider">
                                {{ $log->license_key_used }}
                            </code>
                        @else
                            <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($log->product_name)
                            <p class="text-[10px] font-semibold text-slate-700 dark:text-slate-300">{{ $log->product_name }}</p>
                            <p class="text-[9px] font-mono text-slate-400">{{ $log->script_source ?? '—' }} · {{ $log->script_folder ?? '—' }}</p>
                        @else
                            <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($log->roblox_username)
                            <div>
                                <p class="text-[10px] font-semibold text-slate-700 dark:text-slate-300">{{ $log->roblox_username }}</p>
                                @if ($log->roblox_place_id)
                                    <p class="text-[9px] font-mono text-slate-400 mt-0.5">
                                        {{ \App\Services\ScriptService::getMapNameFromPlaceId($log->roblox_place_id) }}
                                        <span class="text-slate-300 dark:text-slate-600">({{ $log->roblox_place_id }})</span>
                                    </p>
                                @endif
                            </div>
                        @else
                            <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($log->inject_step)
                            <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider {{ $stepColor }}">
                                {{ $log->inject_step }}
                            </span>
                        @else
                            <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="text-[10px] {{ $hasError ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-slate-600 dark:text-slate-400' }}">
                            {{ $log->status }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="text-xs font-bold {{ $http >= 400 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $http }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="text-[10px] {{ $ms > 200 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-400' }}">
                            {{ $ms > 0 ? $ms : '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <button type="button" onclick="viewApiLog({{ $log->id }})"
                            class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                            View
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-400">Tidak ada log ditemukan</p>
                            <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Coba hapus filter atau tunggu request masuk</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <p class="text-xs text-slate-400 font-mono">Hal. {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</p>
        <div class="text-xs">{{ $logs->withQueryString()->links() }}</div>
    </div>
    @endif
</div>

{{-- Legenda inject step --}}
<div class="mt-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Legenda Inject Step</p>
    <div class="flex flex-wrap gap-3">
        @foreach ([
            ['step' => 'script_served', 'color' => 'violet', 'desc' => 'Script berhasil dikirim ke executor (GET /api/license/get atau /s/{token})'],
            ['step' => 'token_created', 'color' => 'blue',    'desc' => 'Token inject dibuat — executor harus download dalam 30 detik'],
            ['step' => 'license_check', 'color' => 'red',     'desc' => 'Gagal validasi lisensi (key, HWID, banned, expired)'],
        ] as $item)
        <div class="flex items-start gap-2 min-w-[200px]">
            <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-{{ $item['color'] }}-100 text-{{ $item['color'] }}-700 dark:bg-{{ $item['color'] }}-900/30 dark:text-{{ $item['color'] }}-400 shrink-0 mt-0.5">
                {{ $item['step'] }}
            </span>
            <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $item['desc'] }}</p>
        </div>
        @endforeach
    </div>
</div>

@push('modals')
<x-allert.app-modal id="modalApiLogDetail" maxWidth="lg" title="Detail Log API" description="Informasi lengkap request inject / license"
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'>
    <div id="apiLogDetailBody" class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
        <p class="text-slate-400">Memuat...</p>
    </div>
    <x-slot name="footer">
        <button onclick="AppModal.close('modalApiLogDetail')" class="modal-btn-cancel">Tutup</button>
    </x-slot>
</x-allert.app-modal>
@endpush

@push('scripts')
<script>
var CSRF_LOG = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function escHtml(s) {
    if (s === null || s === undefined) return '—';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function viewApiLog(id) {
    var body = document.getElementById('apiLogDetailBody');
    body.innerHTML = '<p class="text-slate-400">Memuat detail log...</p>';
    AppModal.open('modalApiLogDetail');

    fetch('/admin/api-logs/' + id, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.ok || !res.log) {
            body.innerHTML = '<p class="text-red-500">Gagal memuat log.</p>';
            return;
        }
        var log = res.log;
        var meta = log.request_meta || {};
        var metaHtml = Object.keys(meta).length
            ? '<pre class="text-[10px] font-mono bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-3 overflow-x-auto max-h-40">' + escHtml(JSON.stringify(meta, null, 2)) + '</pre>'
            : '<span class="text-slate-400">—</span>';

        body.innerHTML = ''
            + '<div class="grid grid-cols-2 gap-3">'
            + '<div><p class="text-[9px] uppercase tracking-widest text-slate-400 mb-1">Waktu</p><p class="font-mono">' + escHtml(log.created_at) + '</p></div>'
            + '<div><p class="text-[9px] uppercase tracking-widest text-slate-400 mb-1">Status</p><p class="font-semibold">' + escHtml(log.status) + ' · HTTP ' + escHtml(log.http_code) + ' · ' + escHtml(log.response_time_ms) + 'ms</p></div>'
            + '<div><p class="text-[9px] uppercase tracking-widest text-slate-400 mb-1">Endpoint</p><p class="font-mono">' + escHtml(log.method) + ' ' + escHtml(log.endpoint) + '</p></div>'
            + '<div><p class="text-[9px] uppercase tracking-widest text-slate-400 mb-1">Inject Step</p><p class="font-mono">' + escHtml(log.inject_step) + '</p></div>'
            + '</div>'
            + '<div class="border-t border-slate-200 dark:border-slate-700 pt-3">'
            + '<p class="text-[9px] uppercase tracking-widest text-slate-400 mb-2">Produk & Script</p>'
            + '<p><strong>Produk:</strong> ' + escHtml(log.product_name) + ' (ID: ' + escHtml(log.product_id) + ')</p>'
            + '<p><strong>Source:</strong> ' + escHtml(log.script_source) + ' · <strong>Folder:</strong> <code>' + escHtml(log.script_folder) + '</code></p>'
            + '</div>'
            + '<div class="border-t border-slate-200 dark:border-slate-700 pt-3">'
            + '<p class="text-[9px] uppercase tracking-widest text-slate-400 mb-2">Lisensi & Roblox</p>'
            + '<p><strong>Key:</strong> <code>' + escHtml(log.license_key_used) + '</code></p>'
            + '<p><strong>HWID:</strong> <code>' + escHtml(log.hwid_used) + '</code></p>'
            + '<p><strong>Roblox:</strong> ' + escHtml(log.roblox_username) + ' · Place ' + escHtml(log.roblox_place_id) + ' (' + escHtml(res.map_name) + ')</p>'
            + '</div>'
            + '<div class="border-t border-slate-200 dark:border-slate-700 pt-3">'
            + '<p class="text-[9px] uppercase tracking-widest text-slate-400 mb-2">Request Meta</p>' + metaHtml
            + '</div>'
            + (log.response_message ? '<div class="border-t border-slate-200 dark:border-slate-700 pt-3"><p class="text-[9px] uppercase tracking-widest text-red-400 mb-1">Error Message</p><p class="font-mono text-red-600 dark:text-red-400 text-[11px]">' + escHtml(log.response_message) + '</p></div>' : '')
            + (log.error_detail ? '<div class="border-t border-slate-200 dark:border-slate-700 pt-3"><p class="text-[9px] uppercase tracking-widest text-slate-400 mb-1">Error Detail</p><pre class="text-[10px] font-mono bg-slate-100 dark:bg-slate-900 border p-3 overflow-x-auto max-h-48">' + escHtml(log.error_detail) + '</pre></div>' : '')
            + '<div class="border-t border-slate-200 dark:border-slate-700 pt-3 text-[10px] font-mono text-slate-400">'
            + '<p>IP: ' + escHtml(log.ip) + '</p>'
            + '<p class="mt-1 break-all">UA: ' + escHtml(log.user_agent) + '</p>'
            + '</div>';
    })
    .catch(function() {
        body.innerHTML = '<p class="text-red-500">Gagal memuat detail log.</p>';
    });
}
</script>
@endpush

@endsection
