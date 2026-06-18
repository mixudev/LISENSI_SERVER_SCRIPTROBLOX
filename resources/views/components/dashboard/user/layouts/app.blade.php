<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-50 antialiased" x-data="{ mobileMenuOpen: false }">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2 font-bold text-gray-900">
                    <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    {{ config('app.name') }}
                </a>

                <div class="hidden sm:flex items-center gap-1">
                    @foreach ([
                        ['route' => 'user.dashboard',          'label' => 'Beranda'],
                        ['route' => 'user.licenses.index',     'label' => 'Lisensi Saya'],
                        ['route' => 'user.activities.index',   'label' => 'Riwayat'],
                        ['route' => 'user.profile.show',       'label' => 'Profil'],
                    ] as $item)
                        <a href="{{ route($item['route']) }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                {{ request()->routeIs($item['route']) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center justify-between text-sm">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 ml-4">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center justify-between text-sm">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-600 ml-4">✕</button>
            </div>
        @endif

        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
