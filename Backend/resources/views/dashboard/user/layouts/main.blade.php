<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    {{-- Dark mode init: must run before ANY render to prevent white flash --}}
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        mono: ['DM Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <style>
        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }

        /* ── Bottom nav mobile ── */
        .mobile-nav-link { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 8px 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; transition: color .15s; flex: 1; text-decoration: none; }
        .mobile-nav-link svg { width: 20px; height: 20px; flex-shrink: 0; }
        .mobile-nav-link.active { color: #7c3aed; }
        .dark .mobile-nav-link { color: #64748b; }
        .dark .mobile-nav-link.active { color: #a78bfa; }

        /* ── Desktop sidebar ── */
        .user-sidebar-link { display: flex; align-items: center; gap: 10px; padding: 8px 12px; font-size: 13px; font-weight: 500; color: #64748b; transition: background .15s, color .15s; text-decoration: none; border: none; background: none; width: 100%; cursor: pointer; }
        .user-sidebar-link:hover { background: #f1f5f9; color: #1e293b; }
        .user-sidebar-link.active { background: #f5f3ff; color: #6d28d9; font-weight: 600; border-left: 2px solid #7c3aed; }
        .dark .user-sidebar-link { color: #94a3b8; }
        .dark .user-sidebar-link:hover { background: #1e293b; color: #e2e8f0; }
        .dark .user-sidebar-link.active { background: rgba(109,40,217,.12); color: #a78bfa; border-left-color: #7c3aed; }

        /* ── Copy badge animation ── */
        @keyframes fadeInUp { from { opacity:0; transform:translateY(4px) } to { opacity:1; transform:translateY(0) } }
        .copy-badge { animation: fadeInUp .2s ease both; }

        /* ── Code block inside tutorial ── */
        .tutorial-code { background: #0f172a; color: #e2e8f0; font-family: 'DM Mono', monospace; font-size: 12px; padding: 14px 16px; overflow-x: auto; line-height: 1.7; position: relative; }
        .dark .tutorial-code { background: #020617; }
        .tutorial-code .c { color: #64748b; } /* comment */
        .tutorial-code .k { color: #a78bfa; } /* keyword */
        .tutorial-code .s { color: #86efac; } /* string */
        .tutorial-code .n { color: #7dd3fc; } /* number/var */
    </style>

    @stack('head')
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased">

{{-- ══ MOBILE TOP BAR ══ --}}
<header class="lg:hidden sticky top-0 z-30 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 h-14 flex items-center px-4 gap-3">
    <div class="flex items-center gap-2 flex-1">
        <div class="w-7 h-7 bg-violet-600 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ config('app.name') }}</span>
    </div>
    <div class="flex items-center gap-1">
        <button onclick="toggleDarkUser()" class="w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle dark mode">
            <svg id="uIconMoon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg id="uIconSun" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        </button>
        <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[11px] font-bold">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
    </div>
</header>

{{-- ══ LAYOUT WRAPPER ══ --}}
<div class="flex min-h-screen">

    {{-- ── DESKTOP SIDEBAR ── --}}
    <aside class="hidden lg:flex flex-col w-60 shrink-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 fixed top-0 left-0 h-full z-20">

        {{-- Logo --}}
        <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-200 dark:border-slate-800 shrink-0">
            <div class="w-8 h-8 bg-violet-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <div class="text-[14px] font-bold text-slate-900 dark:text-white leading-tight">{{ config('app.name') }}</div>
                <div class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">User Panel</div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-4 px-3 overflow-y-auto">
            <p class="px-3 mb-2 text-[10px] font-mono font-semibold uppercase tracking-widest text-slate-400">Menu</p>
            <ul class="space-y-0.5">
                @foreach ([
                    ['route' => 'user.dashboard',        'label' => 'Beranda',      'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
                    ['route' => 'user.licenses.index',   'label' => 'Lisensi Saya', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>'],
                    ['route' => 'user.activities.index', 'label' => 'Riwayat',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                    ['route' => 'user.profile.show',     'label' => 'Profil',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
                ] as $item)
                <li>
                    <a href="{{ route($item['route']) }}"
                        class="user-sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4 shrink-0">{!! $item['icon'] !!}</svg>
                        {{ $item['label'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </nav>

        {{-- Sidebar footer --}}
        <div class="border-t border-slate-200 dark:border-slate-800 p-3 shrink-0 space-y-1">
            <button onclick="toggleDarkUser()" class="user-sidebar-link w-full">
                <svg id="uIconMoonDesk" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg id="uIconSunDesk" class="w-4 h-4 shrink-0 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <span id="uThemeLabel">Mode Gelap</span>
            </button>
            <a href="{{ route('home') }}" class="user-sidebar-link">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Landing Page
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="user-sidebar-link text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 dark:text-red-400 w-full">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>

            {{-- User info --}}
            <div class="flex items-center gap-2.5 px-3 py-2.5 mt-1 border-t border-slate-100 dark:border-slate-800">
                <div class="w-7 h-7 bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <div class="flex-1 lg:ml-60 flex flex-col min-h-screen">

        {{-- Desktop top bar --}}
        <header class="hidden lg:flex sticky top-0 z-10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800 h-16 items-center px-6 gap-4">
            <div>
                <h1 class="text-sm font-semibold text-slate-800 dark:text-slate-200">@yield('title', 'Dashboard')</h1>
                <p class="text-[11px] text-slate-400 font-mono">{{ now()->locale('id')->isoFormat('D MMM Y') }}</p>
            </div>
            <div class="flex-1"></div>
            <span class="text-xs text-slate-400 hidden sm:block">{{ auth()->user()->name }}</span>
        </header>

        <main class="flex-1 p-4 md:p-6 pb-24 lg:pb-6">
            @yield('content')
        </main>

        <footer class="hidden lg:flex px-6 py-3 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 items-center justify-between">
            <span class="text-xs text-slate-400 font-mono">{{ config('app.name') }} · User Panel</span>
            <span class="text-xs text-slate-400">{{ now()->format('Y') }}</span>
        </footer>
    </div>
</div>

{{-- ══ MOBILE BOTTOM NAV ══ --}}
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex">
    @foreach ([
        ['route' => 'user.dashboard',        'label' => 'Beranda',  'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
        ['route' => 'user.licenses.index',   'label' => 'Lisensi',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>'],
        ['route' => 'user.activities.index', 'label' => 'Riwayat',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['route' => 'user.profile.show',     'label' => 'Profil',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
    ] as $item)
    <a href="{{ route($item['route']) }}" class="mobile-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $item['icon'] !!}</svg>
        {{ $item['label'] }}
    </a>
    @endforeach
</nav>

<script>
function toggleDarkUser() {
    var isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    syncDarkIcons(isDark);
}

function syncDarkIcons(isDark) {
    ['uIconMoon','uIconMoonDesk'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', isDark);
    });
    ['uIconSun','uIconSunDesk'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', !isDark);
    });
    var label = document.getElementById('uThemeLabel');
    if (label) label.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
}

// Sync icons on load
(function() {
    syncDarkIcons(document.documentElement.classList.contains('dark'));
})();

// Copy to clipboard helper
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
        setTimeout(function() { btn.innerHTML = orig; }, 1800);
    });
}
</script>

@stack('modals')
@stack('scripts')
</body>
</html>
