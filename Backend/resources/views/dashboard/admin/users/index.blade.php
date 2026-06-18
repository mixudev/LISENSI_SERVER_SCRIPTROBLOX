@extends('dashboard.admin.layouts.main')
@section('title', 'Manajemen Pengguna')
@section('content')

{{-- PAGE HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Pengguna</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $users->total() }} pengguna terdaftar</p>
    </div>
</div>

{{-- FILTER --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="flex-1 min-w-[220px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Cari Nama / Email</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau email..."
                class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
        </div>
    </div>
    <div class="flex gap-2 items-end">
        <button type="submit"
            class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">
            Filter
        </button>
        <a href="{{ route('admin.users.index') }}"
            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
            @if($users->total() > 0)
                Menampilkan
                <strong class="text-slate-700 dark:text-slate-200">{{ $users->firstItem() }}–{{ $users->lastItem() }}</strong>
                dari
                <strong class="text-slate-700 dark:text-slate-200">{{ number_format($users->total()) }}</strong>
                pengguna
            @else
                0 pengguna ditemukan
            @endif
        </span>
        <p class="text-[10px] text-slate-400">Klik baris untuk melihat detail</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Pengguna</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Role</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Lisensi</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Terakhir Login</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Terdaftar</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($users as $user)
                @php
                    $roleVal   = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
                    $roleBadge = $roleVal === 'admin'
                        ? 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-700/40'
                        : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700';
                    $statusBadge = $user->is_active
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40'
                        : 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-700/40';
                    $activeLicenses = $user->licenses->filter(fn($l) => ($l->status instanceof \App\Enums\LicenseStatus ? $l->status->value : $l->status) === 'active')->count();
                @endphp
                <tr class="hover:bg-violet-50/30 dark:hover:bg-violet-900/5 transition-colors cursor-pointer group"
                    onclick="AppModal.open('modalUser-{{ $user->id }}')">
                    {{-- Pengguna --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shrink-0 select-none">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-none">{{ $user->name }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    {{-- Role --}}
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $roleBadge }}">
                            {{ ucfirst($roleVal) }}
                        </span>
                    </td>
                    {{-- Status --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }} shrink-0"></div>
                            <span class="text-xs {{ $user->is_active ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </td>
                    {{-- Lisensi --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $user->licenses_count }}</span>
                            @if ($activeLicenses > 0)
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400">({{ $activeLicenses }} aktif)</span>
                            @endif
                        </div>
                    </td>
                    {{-- Terakhir Login --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs text-slate-400">—</span>
                    </td>
                    {{-- Terdaftar --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $user->created_at->format('d M Y') }}</span>
                    </td>
                    {{-- Aksi --}}
                    <td class="px-5 py-3.5 text-right" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="AppModal.open('modalUser-{{ $user->id }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-700/40 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </button>
                            @if ($user->id !== auth()->id())
                            <button onclick="confirmToggleActive({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->is_active ? 'true' : 'false' }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold transition-colors
                                    {{ $user->is_active
                                        ? 'text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-700/40 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                                        : 'text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-700/40 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' }}">
                                @if ($user->is_active)
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Nonaktifkan
                                @else
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aktifkan
                                @endif
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-400">Tidak ada pengguna ditemukan</p>
                            <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Coba ubah kata kunci pencarian</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <p class="text-xs text-slate-400 font-mono">Hal. {{ $users->currentPage() }} / {{ $users->lastPage() }}</p>
        <div class="text-xs">{{ $users->withQueryString()->links() }}</div>
    </div>
    @endif
</div>

{{-- ══ MODALS DETAIL USER ══ --}}
@foreach ($users as $user)
@push('modals')
@php
    $roleVal     = $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role;
    $roleBadge   = $roleVal === 'admin'
        ? 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-700/40'
        : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700';
    $activeLicenses = $user->licenses->filter(fn($l) => ($l->status instanceof \App\Enums\LicenseStatus ? $l->status->value : $l->status) === 'active')->count();
    $totalLicenses  = $user->licenses->count();
@endphp
<x-allert.app-modal
    id="modalUser-{{ $user->id }}"
    maxWidth="3xl"
    title="{{ $user->name }}"
    description="Detail akun, statistik, dan seluruh lisensi pengguna"
    iconColor="{{ $user->is_active ? 'indigo' : 'red' }}"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'>

    {{-- ── Info & Stats Row ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 border border-slate-200 dark:border-slate-700 divide-x divide-y lg:divide-y-0 divide-slate-200 dark:divide-slate-700 mb-5">
        <div class="p-4">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Email</p>
            <p class="text-xs text-slate-700 dark:text-slate-300 truncate font-medium">{{ $user->email }}</p>
        </div>
        <div class="p-4">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Role</p>
            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $roleBadge }}">
                {{ ucfirst($roleVal) }}
            </span>
        </div>
        <div class="p-4">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Status</p>
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                <span class="text-xs font-semibold {{ $user->is_active ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
        <div class="p-4">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1">Email Verif.</p>
            @if ($user->email_verified_at)
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Terverifikasi</span>
                </div>
            @else
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Belum</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Stat mini cards ── --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $totalLicenses }}</p>
            <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">Total Lisensi</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-700/30 p-3 text-center">
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $activeLicenses }}</p>
            <p class="text-[10px] text-emerald-600 dark:text-emerald-500 uppercase tracking-wider font-semibold mt-0.5">Aktif</p>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3 text-center">
            <p class="text-xl font-bold text-slate-700 dark:text-slate-300">{{ $user->created_at->diffForHumans() }}</p>
            <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">Bergabung</p>
        </div>
    </div>

    {{-- ── Profil detail ── --}}
    <div class="grid grid-cols-2 gap-x-6 gap-y-2 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-0.5">Telepon</p>
            <p class="text-xs text-slate-700 dark:text-slate-300">{{ $user->phone ?? '—' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-0.5">Terdaftar</p>
            <p class="text-xs text-slate-700 dark:text-slate-300">{{ $user->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    {{-- ── Tabel Lisensi ── --}}
    <div class="mb-1 flex items-center justify-between">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
            Lisensi ({{ $totalLicenses }})
        </p>
        @if($totalLicenses > 0)
        <a href="{{ route('admin.licenses.index', ['search' => $user->email]) }}"
            class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">
            Lihat di halaman lisensi →
        </a>
        @endif
    </div>

    @if ($user->licenses->isNotEmpty())
    <div class="border border-slate-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">License Key</th>
                    <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Produk</th>
                    <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">HWID</th>
                    <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Expired</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($user->licenses as $license)
                @php
                    $lsv = $license->status instanceof \App\Enums\LicenseStatus ? $license->status->value : $license->status;
                    $lb  = match($lsv) {
                        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40',
                        'expired'   => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                        'banned'    => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-700/40',
                        'suspended' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-700/40',
                        default     => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                @endphp
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-4 py-2.5">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.licenses.show', $license) }}" target="_blank"
                                class="font-mono text-[10px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                                {{ $license->license_key }}
                            </a>
                            <button onclick="navigator.clipboard.writeText('{{ $license->license_key }}')"
                                class="text-slate-300 hover:text-slate-500 dark:hover:text-slate-300 transition-colors" title="Salin">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400 font-medium">
                        {{ $license->product?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $lb }}">
                            {{ $lsv }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($license->hwid)
                            <div class="flex items-center gap-1">
                                <div class="w-1.5 h-1.5 bg-emerald-500 shrink-0"></div>
                                <code class="text-[10px] font-mono text-slate-500 dark:text-slate-400">
                                    {{ substr($license->hwid, 0, 8) }}…
                                </code>
                            </div>
                        @else
                            <span class="text-[10px] text-slate-300 dark:text-slate-600 italic">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        @if ($license->expired_at)
                            <span class="text-[10px] {{ $license->expired_at->isPast() ? 'text-red-500 dark:text-red-400 font-semibold' : 'text-slate-500 dark:text-slate-400' }}">
                                {{ $license->expired_at->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">Lifetime</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex items-center gap-3 py-6 px-4 border border-dashed border-slate-200 dark:border-slate-700">
        <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
        <p class="text-xs text-slate-400 italic">Pengguna ini belum memiliki lisensi.</p>
    </div>
    @endif

    <x-slot name="footer">
        @if ($user->id !== auth()->id())
        <button
            onclick="AppModal.close('modalUser-{{ $user->id }}'); setTimeout(() => confirmToggleActive({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->is_active ? 'true' : 'false' }}), 350)"
            class="{{ $user->is_active ? 'modal-btn-danger' : 'modal-btn-primary' }}">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                @if ($user->is_active)
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                @else
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @endif
            </svg>
            {{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
        </button>
        @endif
        <button onclick="AppModal.close('modalUser-{{ $user->id }}')" class="modal-btn-cancel">Tutup</button>
    </x-slot>
</x-allert.app-modal>
@endpush
@endforeach

{{-- Hidden form toggle active --}}
<form id="formToggleActive" method="POST" class="hidden">
    @csrf @method('PATCH')
</form>

@push('scripts')
<script>
function confirmToggleActive(userId, userName, isActive) {
    AppPopup.confirm({
        title: isActive ? 'Nonaktifkan Pengguna?' : 'Aktifkan Pengguna?',
        description: isActive
            ? `Akun <strong>${userName}</strong> akan dinonaktifkan dan tidak dapat login sampai diaktifkan kembali.`
            : `Akun <strong>${userName}</strong> akan diaktifkan dan dapat login kembali.`,
        confirmText : isActive ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
        cancelText  : 'Batal',
        onConfirm   : () => {
            const form = document.getElementById('formToggleActive');
            form.action = `/admin/users/${userId}/toggle-active`;
            form.submit();
        }
    });
}
</script>
@endpush

@endsection
