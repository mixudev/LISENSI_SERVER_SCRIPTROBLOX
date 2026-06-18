@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border-b border-slate-100 dark:border-slate-800 h-[70px] flex items-center px-4 md:px-6 gap-4">

    {{-- Mobile menu toggle --}}
    <button onclick="toggleSidebar()"
        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        aria-label="Open sidebar">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Desktop collapse toggle --}}
    <button id="collapseBtn" onclick="toggleCollapse()"
        class="w-9 h-9 items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        aria-label="Toggle sidebar">
        <svg id="collapseBtnIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Page title --}}
    <div class="hidden md:block">
        <h1 class="text-sm font-semibold text-slate-800 dark:text-slate-200">@yield('title', 'Dashboard')</h1>
        <p class="text-[11px] text-slate-400 font-mono">
            Admin Panel · {{ now()->locale('id')->isoFormat('D MMM Y') }}
        </p>
    </div>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Actions --}}
    <div class="flex items-center gap-1.5">

        {{-- Dark mode toggle --}}
        <button onclick="toggleDark()"
            class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            title="Toggle dark mode">
            <svg id="iconMoon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg id="iconSun" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        </button>

        {{-- Notifications --}}
        <div class="relative" id="notifWrapper">
            <button onclick="toggleNotif()"
                class="relative w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"></span>
            </button>

            <div id="notifPanel"
                class="notif-panel absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl shadow-2xl z-50">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Notifikasi</span>
                </div>
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-50 dark:divide-slate-700/50">
                    <div class="px-4 py-4 text-xs text-slate-400 text-center">Tidak ada notifikasi baru.</div>
                </div>
            </div>
        </div>

        <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>

        {{-- Profile dropdown --}}
        <div class="relative" id="profileDropdownWrapper">
            <button onclick="toggleDropdown()"
                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[11px] font-semibold flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <span class="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $user->name }}</span>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="profileDropdown"
                class="hidden absolute right-0 top-full mt-2 w-52 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50">
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-700 mb-1">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $user->name }}</div>
                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                </div>

                <a href="{{ route('home') }}"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Landing Page
                </a>

                <div class="border-t border-slate-100 dark:border-slate-700 mt-1 pt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
