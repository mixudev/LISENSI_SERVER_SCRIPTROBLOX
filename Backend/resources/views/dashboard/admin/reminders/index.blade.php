@extends('dashboard.admin.layouts.main')
@section('title', 'Daftar Pengingat Discord')
@section('content')

{{-- PAGE HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Pengingat Discord (Reminders)</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ count($reminders) }} Pengingat aktif dijadwalkan</p>
    </div>
</div>

{{-- CARDS STATS --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Total Pengingat Dijadwalkan</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">{{ count($reminders) }} Sesi</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Pengingat Men-tag Role/User</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">
                    {{ count(array_filter($reminders, fn($r) => !empty($r['targetTag']))) }} Pengingat
                </p>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Daftar Pengingat yang Sedang Berjalan di Latar Belakang Bot</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Pembuat Pengingat</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Pesan Pengingat</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Waktu Dijadwalkan</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Target Tag</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($reminders as $reminder)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                    {{-- User Pembuat --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if ($reminder['user'] && !empty($reminder['user']['avatar']))
                                <img src="{{ $reminder['user']['avatar'] }}" alt="Avatar" class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-slate-800 dark:text-slate-200 leading-none">
                                    {{ $reminder['user'] ? $reminder['user']['name'] : 'Guest/Discord Member' }}
                                </p>
                                <p class="text-[10px] text-slate-400 font-mono mt-1">ID: {{ $reminder['userId'] }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Pesan --}}
                    <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300 max-w-xs truncate font-medium">
                        {{ $reminder['message'] }}
                    </td>

                    {{-- Waktu --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs text-slate-700 dark:text-slate-300 font-mono">
                            {{ \Carbon\Carbon::parse($reminder['fireAt'])->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                        </span>
                        <span class="block text-[10px] text-slate-400 mt-0.5">WIB</span>
                    </td>

                    {{-- Target Tag --}}
                    <td class="px-5 py-3.5">
                        @if ($reminder['targetTag'])
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-400 border border-violet-100 dark:border-violet-900/40 rounded">
                                {{ $reminder['targetTag'] }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 italic">Hanya Pembuat</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-3.5">
                        @php
                            $isExpired = \Carbon\Carbon::parse($reminder['fireAt'])->isPast();
                        @endphp
                        @if ($isExpired)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Memproses / Telat
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                Scheduled (Aktif)
                            </span>
                        @endif
                    </td>

                    {{-- Action --}}
                    <td class="px-5 py-3.5 text-right">
                        <form action="{{ route('admin.reminders.destroy', $reminder['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengingat ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Batalkan Pengingat">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500 italic">
                        Tidak ada pengingat aktif yang sedang dijadwalkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
