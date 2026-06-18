@php
    $inputClass = 'w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white outline-none placeholder:text-white/25 focus:border-violet-500/50 focus:ring-4 focus:ring-violet-500/15';
    $inputError = 'border-red-400/60 bg-red-500/5 focus:border-red-400/60 focus:ring-red-500/15';
    $labelClass = 'mb-1.5 block text-sm font-medium text-white/70';
    $btnSubmit = 'mt-1 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 py-3 text-sm font-medium text-white shadow-lg shadow-violet-900/30 transition hover:-translate-y-px hover:from-violet-500 hover:to-violet-600';
@endphp

<x-layouts.auth title="Reset Password">
    <h1 class="mb-1 text-xl font-semibold text-white">Buat password baru</h1>
    <p class="mb-6 text-sm text-white/45">Masukkan password baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="{{ $labelClass }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                class="{{ $inputClass }} {{ $errors->has('email') ? $inputError : '' }}">
            @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="{{ $labelClass }}">Password Baru</label>
            <input id="password" type="password" name="password" required placeholder="Min. 8 karakter"
                class="{{ $inputClass }} {{ $errors->has('password') ? $inputError : '' }}">
            @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi password baru"
                class="{{ $inputClass }}">
        </div>

        <button type="submit" class="{{ $btnSubmit }}">
            <i class="ti ti-lock-check text-sm" aria-hidden="true"></i> Reset Password
        </button>
    </form>
</x-layouts.auth>
