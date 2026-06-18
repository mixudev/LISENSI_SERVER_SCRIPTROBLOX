<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-10">
    <div class="flex items-center gap-4">
        {{-- Hamburger for mobile --}}
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Page title --}}
        @isset($title)
            <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
        @endisset
    </div>

    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-600 hidden sm:block">
            {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
        </span>
        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</header>
