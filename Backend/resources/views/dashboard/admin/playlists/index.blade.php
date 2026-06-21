@extends('dashboard.admin.layouts.main')
@section('title', 'Daftar Playlist Discord')
@section('content')

{{-- PAGE HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Playlist Lagu Lofi User</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ count($playlists) }} User memiliki playlist pribadi</p>
    </div>
</div>

{{-- CARDS STATS --}}
@php
    $totalSongs = 0;
    foreach ($playlists as $pl) {
        $totalSongs += count($pl['tracks']);
    }
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7.5 7.5 0 0 1-13.5 3M15 8.5a4.5 4.5 0 0 1-8 2.5M12 3v1m8.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Total Playlist Terdaftar</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">{{ count($playlists) }} Playlist</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-fuchsia-50 dark:bg-fuchsia-950/40 text-fuchsia-600 dark:text-fuchsia-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2Zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2ZM9 10l12-3" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Total Keseluruhan Lagu</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">{{ $totalSongs }} Lagu</p>
            </div>
        </div>
    </div>
</div>

{{-- GRID PLAYLISTS --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    @forelse ($playlists as $playlist)
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col h-full">
        {{-- Card Header --}}
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
            <div class="flex items-center gap-3">
                @if ($playlist['user'] && !empty($playlist['user']['avatar']))
                    <img src="{{ $playlist['user']['avatar'] }}" alt="Avatar" class="w-9 h-9 rounded-full border border-slate-200 dark:border-slate-700 shrink-0">
                @else
                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-200 leading-tight">
                        {{ $playlist['user'] ? $playlist['user']['name'] : 'Guest/Discord Member' }}
                    </h3>
                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">Guild ID: {{ $playlist['guildId'] }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-400 border border-purple-200 dark:border-purple-800 rounded">
                    {{ count($playlist['tracks']) }} Lagu
                </span>
                
                <form action="{{ route('admin.playlists.destroy-playlist', $playlist['key']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh playlist user ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 rounded hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors" title="Hapus Seluruh Playlist">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Card Body (Tracklist) --}}
        <div class="flex-1 p-5 overflow-y-auto max-h-[300px]">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach ($playlist['tracks'] as $index => $track)
                <li class="py-2.5 flex items-center justify-between gap-4 group">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="text-xs font-mono text-slate-400 shrink-0 w-4">#{{ $index + 1 }}</span>
                        <div class="truncate">
                            <p class="font-semibold text-slate-700 dark:text-slate-300 truncate text-xs">
                                {{ $track['title'] }}
                            </p>
                            <a href="{{ $track['url'] }}" target="_blank" class="text-[10px] text-violet-500 hover:underline inline-flex items-center gap-0.5 mt-0.5">
                                {{ Str::limit($track['url'], 40) }}
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <form action="{{ route('admin.playlists.destroy-track', [$playlist['key'], $index]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lagu ini dari playlist?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 rounded hover:bg-red-50 text-slate-300 hover:text-red-500 dark:hover:bg-red-950/20 transition-colors" title="Hapus Lagu">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center text-slate-400 dark:text-slate-500 italic">
        Belum ada user yang menyimpan playlist lagu lofi.
    </div>
    @endforelse
</div>

@endsection
