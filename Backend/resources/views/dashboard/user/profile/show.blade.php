@extends('dashboard.user.layouts.main')
@section('title', 'Profil')
@section('content')

<div class="max-w-2xl flex flex-col gap-6">

    {{-- Edit Profil --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-5">Informasi Profil</h2>

        <form method="POST" action="{{ route('user.profile.update') }}" class="flex flex-col gap-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3 py-2 border text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500
                        {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-slate-700' }}">
                @error('name')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 text-sm bg-slate-50 dark:bg-slate-800/50 text-slate-500 cursor-not-allowed">
                <p class="text-[10px] text-slate-400 mt-1">Email tidak dapat diubah.</p>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>

            <button type="submit"
                class="self-start px-5 py-2 bg-violet-600 text-white text-[10px] font-bold uppercase tracking-wider hover:bg-violet-700 transition-colors">
                Simpan
            </button>
        </form>
    </div>

    {{-- Ganti Password --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-5">Ganti Password</h2>

        <form method="POST" action="{{ route('user.profile.password') }}" class="flex flex-col gap-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Password Saat Ini</label>
                <input type="password" name="current_password" required
                    class="w-full px-3 py-2 border text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500
                        {{ $errors->has('current_password') ? 'border-red-400' : 'border-slate-200 dark:border-slate-700' }}">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Password Baru</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
                @error('password')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>

            <button type="submit"
                class="self-start px-5 py-2 bg-slate-800 dark:bg-slate-700 text-white text-[10px] font-bold uppercase tracking-wider hover:bg-slate-900 dark:hover:bg-slate-600 transition-colors">
                Ganti Password
            </button>
        </form>
    </div>
</div>

@endsection
