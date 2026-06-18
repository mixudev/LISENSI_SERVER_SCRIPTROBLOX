<x-layouts.auth title="Lupa Password">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Lupa password?</h1>
    <p class="text-sm text-gray-500 mb-6">Masukkan email Anda dan kami akan kirimkan link reset password.</p>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
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

        <button type="submit"
            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition-colors">
            Kirim Link Reset
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Ingat password?
        <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Kembali masuk</a>
    </p>
</x-layouts.auth>
