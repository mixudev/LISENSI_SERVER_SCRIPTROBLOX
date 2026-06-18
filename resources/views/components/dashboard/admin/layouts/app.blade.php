<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-100 antialiased" x-data="{ sidebarOpen: false }">

    {{-- Sidebar --}}
    @include('dashboard.admin.partials.sidebar')

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-20 lg:hidden"></div>

    {{-- Main content --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">
        @include('dashboard.admin.partials.topbar')

        <main class="flex-1 p-6">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center justify-between text-sm">
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-600 hover:text-green-800 ml-4">✕</button>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                    class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center justify-between text-sm">
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-600 hover:text-red-800 ml-4">✕</button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
