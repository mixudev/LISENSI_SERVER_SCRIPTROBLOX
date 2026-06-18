@extends('dashboard.admin.layouts.main')
@section('title', 'Dashboard')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Dashboard</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">Ringkasan sistem lisensi</p>
    </div>
    <span class="text-[10px] font-mono text-slate-400">{{ now()->format('d M Y, H:i') }}</span>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    @foreach ([
        ['label' => 'Lisensi Aktif',    'key' => 'active_licenses',  'color' => 'violet',
         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>'],
        ['label' => 'Total Pengguna',   'key' => 'total_users',      'color' => 'emerald',
         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ['label' => 'Request Hari Ini', 'key' => 'requests_today',   'color' => 'blue',
         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
        ['label' => 'Expired 7 Hari',   'key' => 'expiring_soon',    'color' => 'amber',
         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['label' => 'Online Roblox',    'key' => 'roblox_active',    'color' => 'rose',
         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M9.879 16.121A3 3 0 1014.12 7.88m-4.242 8.243L12 12l-2.122 4.121z"/>'],
    ] as $card)
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ $card['label'] }}</p>
            <div class="w-8 h-8 bg-{{ $card['color'] }}-50 dark:bg-{{ $card['color'] }}-900/20 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    {!! $card['icon'] !!}
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($stats[$card['key']]) }}</p>
        @if ($card['key'] === 'roblox_active')
            <p class="text-[9px] text-slate-400 font-mono mt-1">10 menit terakhir</p>
        @endif
    </div>
    @endforeach
</div>

{{-- STATUS DISTRIBUTION --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 mb-6">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Distribusi Status Lisensi</h3>
        <a href="{{ route('admin.licenses.index') }}" class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">Lihat semua →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-slate-100 dark:divide-slate-800">
        @foreach ([
            'active'    => ['Aktif',      'emerald', 'bg-emerald-50/50 dark:bg-emerald-900/10'],
            'expired'   => ['Kadaluarsa', 'slate',   'bg-slate-50 dark:bg-slate-800/40'],
            'banned'    => ['Dibanned',   'red',     'bg-red-50/50 dark:bg-red-900/10'],
            'suspended' => ['Disuspend',  'amber',   'bg-amber-50/50 dark:bg-amber-900/10'],
        ] as $status => [$lbl, $col, $bg])
        <a href="{{ route('admin.licenses.index', ['status' => $status]) }}"
            class="p-5 {{ $bg }} hover:opacity-80 transition-opacity">
            <p class="text-2xl font-bold text-{{ $col }}-700 dark:text-{{ $col }}-400">
                {{ number_format($licenseStatusCounts[$status] ?? 0) }}
            </p>
            <p class="text-[10px] font-semibold uppercase tracking-widest text-{{ $col }}-500 dark:text-{{ $col }}-600 mt-1.5">{{ $lbl }}</p>
        </a>
        @endforeach
    </div>
</div>

{{-- ROBLOX ACTIVE SESSIONS --}}
@if ($robloxActiveSessions->isNotEmpty())
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 mb-6">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
            </span>
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Sesi Roblox Aktif</h3>
            <span class="text-[10px] font-mono text-rose-500 dark:text-rose-400">{{ $robloxActiveSessions->count() }} online</span>
        </div>
        <span class="text-[10px] text-slate-400 font-mono">10 menit terakhir</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Username Roblox</th>
                    <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Map / Game</th>
                    <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">License Key</th>
                    <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Pemilik</th>
                    <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($robloxActiveSessions as $session)
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $session->roblox_username }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        @if ($session->roblox_place_id)
                            <div>
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ \App\Services\ScriptService::getMapNameFromPlaceId($session->roblox_place_id) }}
                                </span>
                                <code class="block text-[9px] font-mono text-slate-400 mt-0.5">{{ $session->roblox_place_id }}</code>
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Unknown</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <code class="text-[10px] font-mono font-semibold text-violet-600 dark:text-violet-400">{{ $session->license_key }}</code>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs text-slate-600 dark:text-slate-400">{{ $session->user?->name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-[10px] text-slate-400 font-mono">{{ $session->last_used_at?->diffForHumans() }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- RECENT ACTIVITIES + EXPIRING SOON --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Aktivitas Terbaru</h3>
            <a href="{{ route('admin.activities.index') }}" class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">Lihat semua →</a>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
            @forelse ($recentActivities as $activity)
            @php
                $actVal = $activity->action instanceof \BackedEnum ? $activity->action->value : $activity->action;
                $dotColor = match(true) {
                    in_array($actVal, ['login','logout'])                     => 'bg-blue-500',
                    $actVal === 'reset_hwid'                                  => 'bg-amber-500',
                    $actVal === 'download_product'                            => 'bg-purple-500',
                    $actVal === 'license_activated'                           => 'bg-emerald-500',
                    in_array($actVal, ['license_banned','license_suspended']) => 'bg-red-500',
                    default                                                   => 'bg-slate-400',
                };
            @endphp
            <div class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                <div class="flex items-center justify-center w-7 h-7 bg-slate-100 dark:bg-slate-800 shrink-0">
                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300">
                        {{ strtoupper(substr($activity->user?->name ?? '?', 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-700 dark:text-slate-300 truncate">
                        <span class="font-semibold">{{ $activity->user?->name ?? 'System' }}</span>
                        <span class="text-slate-300 dark:text-slate-600 mx-1">·</span>
                        <span class="text-slate-500 dark:text-slate-400">{{ str_replace('_', ' ', $actVal) }}</span>
                    </p>
                    @if ($activity->license)
                    <a href="{{ route('admin.licenses.show', $activity->license) }}"
                        class="text-[10px] font-mono text-violet-500 dark:text-violet-400 hover:underline">
                        {{ $activity->license->license_key }}
                    </a>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <div class="w-1.5 h-1.5 {{ $dotColor }}"></div>
                    <span class="text-[10px] text-slate-400">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-xs text-slate-400">Belum ada aktivitas.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Akan Expired</h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Dalam 7 hari ke depan</p>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
            @forelse ($expiringSoon as $license)
            <div class="px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                <a href="{{ route('admin.licenses.show', $license) }}"
                    class="font-mono text-[11px] font-semibold text-violet-600 dark:text-violet-400 hover:underline block truncate">
                    {{ $license->license_key }}
                </a>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $license->user?->name ?? 'Unassigned' }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">{{ $license->expired_at->diffForHumans() }}</span>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $license->expired_at->format('d/m/Y') }}</span>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-xs text-slate-400">Tidak ada lisensi yang akan expired.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
