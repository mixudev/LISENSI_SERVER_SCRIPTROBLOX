@php
    $inputClass = 'w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white outline-none placeholder:text-white/25 focus:border-violet-500/50 focus:ring-4 focus:ring-violet-500/15';
    $inputError = 'border-red-400/60 bg-red-500/5 focus:border-red-400/60 focus:ring-red-500/15';
    $labelClass = 'mb-1.5 block text-sm font-medium text-white/70';
    $btnSubmit = 'mt-1 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 py-3 text-sm font-medium text-white shadow-lg shadow-violet-900/30 transition hover:-translate-y-px hover:from-violet-500 hover:to-violet-600';
    $linkClass = 'font-medium text-violet-300 no-underline transition hover:text-violet-200';
@endphp

<x-layouts.auth title="Lupa Password">
    <h1 class="mb-1 text-xl font-semibold text-white">Lupa password?</h1>
    <p class="mb-6 text-sm text-white/45">Masukkan email Anda dan kami akan kirimkan link reset password.</p>

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-3.5 py-3 text-sm text-emerald-300">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="email" class="{{ $labelClass }}">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com"
                class="{{ $inputClass }} {{ $errors->has('email') ? $inputError : '' }}">
            @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="{{ $btnSubmit }}">
            <i class="ti ti-mail-forward text-sm" aria-hidden="true"></i> Kirim Link Reset
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-white/45">
        Ingat password? <a href="{{ route('login') }}" class="{{ $linkClass }}">Kembali masuk</a>
    </p>
</x-layouts.auth>
