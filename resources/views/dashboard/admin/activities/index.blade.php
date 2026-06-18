@extends('dashboard.admin.layouts.main')
@section('title', 'Aktivitas Pengguna')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Aktivitas</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $activities->total() }} aktivitas tercatat</p>
    </div>
</div>

<form method="GET" class="flex flex-wrap items-end gap-3 mb-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="min-w-[180px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Jenis Aksi</label>
        <select name="action" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            <option value="">Semua Aksi</option>
            @foreach ($actions as $action)
                @php $actVal = $action instanceof \BackedEnum ? $action->value : $action; @endphp
                <option value="{{ $actVal }}" @selected(request('action') === $actVal)>
                    {{ str_replace('_', ' ', ucfirst($actVal)) }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Dari</label>
        <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
    </div>
    <div>
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Sampai</label>
        <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">Filter</button>
        <a href="{{ route('admin.activities.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Reset</a>
    </div>
</form>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
            @if($activities->total() > 0)
                <strong class="text-slate-700 dark:text-slate-200">{{ $activities->firstItem() }}–{{ $activities->lastItem() }}</strong>
                dari <strong class="text-slate-700 dark:text-slate-200">{{ number_format($activities->total()) }}</strong> aktivitas
            @else
                0 aktivitas ditemukan
            @endif
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Waktu</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">User</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Lisensi</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">IP</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Meta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($activities as $activity)
                @php
                    $actVal = $activity->action instanceof \BackedEnum ? $activity->action->value : $activity->action;
                    $badgeClass = match(true) {
                        in_array($actVal, ['login','logout'])                     => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-700/40',
                        $actVal === 'reset_hwid'                                  => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-700/40',
                        $actVal === 'download_product'                            => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-700/40',
                        $actVal === 'license_activated'                           => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40',
                        in_array($actVal, ['license_banned','license_suspended']) => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-700/40',
                        default                                                   => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                    };
                    $dotColor = match(true) {
                        in_array($actVal, ['login','logout'])                     => 'bg-blue-500',
                        $actVal === 'reset_hwid'                                  => 'bg-amber-500',
                        $actVal === 'download_product'                            => 'bg-purple-500',
                        $actVal === 'license_activated'                           => 'bg-emerald-500',
                        in_array($actVal, ['license_banned','license_suspended']) => 'bg-red-500',
                        default                                                   => 'bg-slate-400',
                    };
                @endphp
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 {{ $dotColor }} shrink-0"></div>
                            <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400">{{ $activity->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-[10px] text-slate-300 dark:text-slate-600 ml-3">{{ $activity->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($activity->user)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[9px] font-bold shrink-0">
                                {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-none">{{ $activity->user->name }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $activity->user->email }}</p>
                            </div>
                        </div>
                        @else
                        <span class="text-xs text-slate-400 italic">System</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                            {{ str_replace('_', ' ', $actVal) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($activity->license)
                        <a href="{{ route('admin.licenses.show', $activity->license) }}"
                            class="font-mono text-[10px] font-semibold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 px-1.5 py-0.5 tracking-wider hover:underline">
                            {{ $activity->license->license_key }}
                        </a>
                        @else
                        <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-[11px] font-mono text-slate-400">{{ $activity->ip ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        @if (!empty($activity->meta))
                        <span class="text-[10px] text-slate-400 font-mono truncate max-w-[140px] block"
                            title="{{ is_array($activity->meta) ? json_encode($activity->meta) : $activity->meta }}">
                            {{ is_array($activity->meta) ? json_encode($activity->meta) : $activity->meta }}
                        </span>
                        @else
                        <span class="text-[10px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-400">Tidak ada aktivitas ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($activities->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <p class="text-xs text-slate-400 font-mono">Hal. {{ $activities->currentPage() }} / {{ $activities->lastPage() }}</p>
        <div class="text-xs">{{ $activities->withQueryString()->links() }}</div>
    </div>
    @endif
</div>

@endsection
