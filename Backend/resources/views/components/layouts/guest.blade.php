@props([
    'title' => null,
    'showNavbar' => false,
    'showOrbs' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.31.0/fonts/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="min-h-screen bg-[#050508] text-slate-200 font-sans antialiased overflow-x-hidden">
    @if ($showOrbs)
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(circle,rgba(139,120,255,0.15)_1px,transparent_1px)] bg-size-[32px_32px] opacity-30"></div>
        <div class="pointer-events-none fixed -top-32 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-violet-600/10 blur-3xl"></div>
    @endif

    @if ($showNavbar)
        @include('partials.guest.navbar')
    @endif

    {{ $slot }}

    @stack('scripts')
</body>
</html>
