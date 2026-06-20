@extends('dashboard.user.layouts.main')
@section('title', 'Riwayat Aktivitas')
@section('content')

{{-- Filter --}}
<form method="GET" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Jenis Aksi</label>
        <select name="action" class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
            <option value="">Semua</option>
            @foreach ($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>
                    {{ \App\Models\LicenseActivity::labelFor($action) }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Dari</label>
        <input type="date" name="from" value="{{ request('from') }}"
            class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
    </div>
    <div>
        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Sampai</label>
        <input type="date" name="to" value="{{ request('to') }}"
            class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
    </div>
    <button type="submit" class="px-4 py-2 bg-violet-600 text-white text-[10px] font-bold uppercase tracking-wider hover:bg-violet-700 transition-colors">
        Filter
    </button>
</form>

{{-- Timeline --}}
@if ($activities->isEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-12 text-center">
        <p class="text-sm text-slate-400">Belum ada riwayat aktivitas.</p>
    </div>
@else
    <div class="border-l-2 border-slate-200 dark:border-slate-700 ml-3">
        @foreach ($activities as $activity)
            @php
                $dotColor = match($activity->action) {
                    'login', 'logout' => 'bg-violet-500',
                    'reset_hwid' => 'bg-orange-500',
                    'download_product' => 'bg-indigo-500',
                    'license_activated' => 'bg-emerald-500',
                    'license_banned', 'license_suspended' => 'bg-red-500',
                    'renew_license' => 'bg-teal-500',
                    default => 'bg-slate-400',
                };
            @endphp
            <div class="relative pl-8 pb-6">
                <div class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-950 {{ $dotColor }}"></div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="flex items-center justify-between gap-4 mb-1">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ \App\Models\LicenseActivity::labelFor($activity->action) }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-400 shrink-0">{{ $activity->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if ($activity->license)
                        <p class="text-xs font-mono text-slate-500 dark:text-slate-400">{{ $activity->license->license_key }}</p>
                    @endif
                    @if ($activity->ip)
                        <p class="text-[10px] text-slate-400 mt-1">IP: {{ $activity->ip }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if ($activities->hasPages())
        <div class="mt-4">
            {{ $activities->withQueryString()->links() }}
        </div>
    @endif
@endif

@endsection
