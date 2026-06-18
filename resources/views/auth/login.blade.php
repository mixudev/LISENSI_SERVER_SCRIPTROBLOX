<x-layouts.auth title="Masuk">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Selamat datang kembali</h1>
    <p class="text-sm text-gray-500 mb-6">Masuk ke akun Anda</p>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                    {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:underline">Lupa password?</a>
            </div>
            <input id="password" type="password" name="password" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember" type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
            <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
        </div>

        <button type="submit"
            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition-colors">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:underline">Daftar sekarang</a>
    </p>
</x-layouts.auth>
