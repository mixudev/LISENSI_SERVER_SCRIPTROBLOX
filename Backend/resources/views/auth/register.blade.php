@php
    $inputClass = 'w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white outline-none placeholder:text-white/25 focus:border-violet-500/50 focus:ring-4 focus:ring-violet-500/15';
    $inputError = 'border-red-400/60 bg-red-500/5 focus:border-red-400/60 focus:ring-red-500/15';
    $labelClass = 'mb-1.5 block text-sm font-medium text-white/70';
    $btnSubmit = 'mt-1 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 py-3 text-sm font-medium text-white shadow-lg shadow-violet-900/30 transition hover:-translate-y-px hover:from-violet-500 hover:to-violet-600';
    $linkClass = 'font-medium text-violet-300 no-underline transition hover:text-violet-200';
@endphp

<x-layouts.auth title="Daftar">
    <h1 class="mb-1 text-xl font-semibold text-white">Buat akun baru</h1>
    <p class="mb-6 text-sm text-white/45">Daftar untuk mengelola lisensi Anda</p>

    <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe"
                class="{{ $inputClass }} {{ $errors->has('name') ? $inputError : '' }}">
            @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="{{ $labelClass }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com"
                class="{{ $inputClass }} {{ $errors->has('email') ? $inputError : '' }}">
            @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="{{ $labelClass }}">Password</label>
            <input id="password" type="password" name="password" required placeholder="Min. 8 karakter"
                class="{{ $inputClass }} {{ $errors->has('password') ? $inputError : '' }}">
            @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi password"
                class="{{ $inputClass }}">
        </div>

        <button type="submit" class="{{ $btnSubmit }}">
            <i class="ti ti-user-plus text-sm" aria-hidden="true"></i> Buat Akun
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-white/45">
        Sudah punya akun? <a href="{{ route('login') }}" class="{{ $linkClass }}">Masuk</a>
    </p>
</x-layouts.auth>
