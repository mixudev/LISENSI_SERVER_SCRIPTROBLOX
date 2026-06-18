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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

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

    {{-- CSS inline — tidak bergantung APP_URL sehingga berfungsi di ngrok/any host --}}
    <style>
        body { transition: background-color 200ms, color 200ms; }
        #authChart { max-height: 220px; }
        .notif-panel { transform: translateY(-8px); opacity: 0; pointer-events: none; transition: transform 200ms ease, opacity 200ms ease; }
        .notif-panel.open { transform: translateY(0); opacity: 1; pointer-events: auto; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        /* ── SIDEBAR LINK ── */
        .sidebar-link { display:flex; align-items:center; gap:12px; padding:8px 12px; border-radius:8px; font-size:14px; transition:background 150ms,color 150ms; position:relative; white-space:nowrap; overflow:hidden; width:100%; box-sizing:border-box; }
        .sidebar-link:not(.active) { color:#64748b; }
        .dark .sidebar-link:not(.active) { color:#94a3b8; }
        .sidebar-link:not(.active):hover { background:#f8fafc; color:#0f172a; }
        .dark .sidebar-link:not(.active):hover { background:#1e293b; color:#f1f5f9; }
        .sidebar-link.active { background:#ede9fe; color:#6d28d9; font-weight:600; }
        .dark .sidebar-link.active { background:rgba(109,40,217,0.15); color:#a78bfa; }
        /* ── SIDEBAR WIDTH ── */
        #sidebar { transition:width 300ms ease-in-out,transform 300ms ease; }
        #sidebar.collapsed { width:5rem; }
        #sidebar:not(.collapsed) { width:16rem; }
        .main-wrapper { transition:padding-left 300ms ease-in-out; }
        .main-wrapper.sidebar-collapsed { padding-left:5rem; }
        .main-wrapper:not(.sidebar-collapsed) { padding-left:16rem; }
        .sidebar-icon { display:flex; align-items:center; justify-content:center; width:16px; height:16px; flex-shrink:0; }
        /* ── COLLAPSED ── */
        #sidebar.collapsed .sidebar-link { width:40px!important; height:40px!important; padding:0!important; margin:0 auto!important; display:flex!important; align-items:center!important; justify-content:center!important; gap:0!important; overflow:visible!important; }
        #sidebar.collapsed .sidebar-label,#sidebar.collapsed .sidebar-badge,#sidebar.collapsed .sidebar-dot-badge { display:none!important; }
        #sidebar.collapsed #sidebarNav { padding-left:0!important; padding-right:0!important; }
        #sidebar.collapsed #sidebarNav > div { padding:0!important; }
        #sidebar.collapsed .sidebar-section-title { display:none!important; }
        #sidebar.collapsed #sidebarNav li { width:100%; display:flex; justify-content:center; margin:0; padding:2px 0; }
        .sidebar-label,.sidebar-section-title,.sidebar-badge,.sidebar-dot-badge,.sidebar-user-info,.sidebar-user-chevron { overflow:hidden; white-space:nowrap; }
        #sidebar-logo-area { transition:padding 300ms ease-in-out; }
        #sidebar.collapsed #sidebar-logo-area { justify-content:center!important; padding:20px 0!important; gap:0!important; }
        #sidebar:not(.collapsed) #sidebar-logo-area { padding:20px 16px; }
        #sidebar.collapsed #logo-text { display:none; }
        #sidebar.collapsed #sidebar-footer-area { padding:12px 0!important; justify-content:center; }
        #sidebar:not(.collapsed) #sidebar-footer-area { padding:12px; }
        #sidebar.collapsed #user-card { width:40px!important; height:40px!important; padding:0!important; margin:0 auto!important; justify-content:center!important; gap:0!important; }
        #sidebar.collapsed .sidebar-user-info,#sidebar.collapsed .sidebar-user-chevron { display:none!important; }
        #sidebar:not(.collapsed) #user-card { padding:10px 12px; }
        /* ── TOOLTIP ── */
        .sidebar-tooltip { position:fixed; left:5rem; top:auto; transform:translateY(-50%); margin-left:8px; background:#0f172a; color:#fff; font-size:12px; padding:4px 10px; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.25); white-space:nowrap; opacity:0; pointer-events:none; transition:opacity 150ms ease; z-index:9999; }
        .dark .sidebar-tooltip { background:#1e293b; border:1px solid #334155; }
        .sidebar-tooltip::before { content:''; position:absolute; right:100%; top:50%; transform:translateY(-50%); border:5px solid transparent; border-right-color:#0f172a; }
        .dark .sidebar-tooltip::before { border-right-color:#1e293b; }
        #sidebar.collapsed .sidebar-link:hover .sidebar-tooltip { opacity:1; }
        #sidebar:not(.collapsed) .sidebar-tooltip { display:none; }
        /* ── COLLAPSE BTN ── */
        #collapseBtn { display:none; }
        @media (min-width:1024px) { #collapseBtn { display:flex; } }
        @media (max-width:1023px) { #sidebar { width:16rem!important; } .main-wrapper { padding-left:0!important; } }
        #collapseBtnIcon { transition:transform 300ms ease-in-out; }
        body.sidebar-is-collapsed #collapseBtnIcon { transform:rotate(180deg); }
        /* ── DROPDOWN ── */
        .sidebar-submenu { display:grid; grid-template-rows:0fr; transition:grid-template-rows 260ms cubic-bezier(.16,1,.3,1); padding-left:20px; margin-left:14px; border-left:1px solid #f1f5f9; }
        .dark .sidebar-submenu { border-left-color:#1e293b; }
        .sidebar-link.dropdown-open + .sidebar-submenu { grid-template-rows:1fr; margin-top:4px; margin-bottom:8px; }
        .sidebar-submenu-inner { overflow:hidden; display:flex; flex-direction:column; gap:2px; opacity:0; transform:translateY(-4px) scaleY(.98); transform-origin:top; transition:opacity 220ms ease,transform 220ms cubic-bezier(.16,1,.3,1); }
        .sidebar-link.dropdown-open + .sidebar-submenu .sidebar-submenu-inner { opacity:1; transform:translateY(0) scaleY(1); }
        .dropdown-chevron { transition:transform 200ms cubic-bezier(.16,1,.3,1); }
        .sidebar-link.dropdown-open .dropdown-chevron { transform:rotate(180deg); }
        .sidebar-link.dropdown-open { background:#f8fafc; color:#0f172a; }
        .dark .sidebar-link.dropdown-open { background:#1e293b; color:#f1f5f9; }
        .sidebar-submenu li { opacity:0; transform:translateY(-4px); transition:opacity 200ms ease,transform 200ms cubic-bezier(.16,1,.3,1),color 150ms; }
        .sidebar-link.dropdown-open + .sidebar-submenu li { opacity:1; transform:translateY(0); }
        .sidebar-link.dropdown-open + .sidebar-submenu li:nth-child(1) { transition-delay:40ms; }
        .sidebar-link.dropdown-open + .sidebar-submenu li:nth-child(2) { transition-delay:80ms; }
        .sidebar-link.dropdown-open + .sidebar-submenu li:nth-child(3) { transition-delay:120ms; }
        .sidebar-submenu .sidebar-link { transition:transform 150ms ease,color 150ms; background:transparent!important; }
        .sidebar-submenu .sidebar-link:hover { transform:translateX(2px); color:#0f172a; }
        .dark .sidebar-submenu .sidebar-link:hover { color:#f1f5f9; }
        #sidebar.collapsed .sidebar-submenu { display:none!important; }
    </style>

    @stack('head')
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased">

    <div id="sidebarOverlay"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden lg:hidden"
        onclick="toggleSidebar()">
    </div>

    @include('dashboard.admin.partials.sidebar')

    <div id="mainWrapper" class="main-wrapper flex flex-col min-h-screen">

        @include('dashboard.admin.partials.header')

        <main class="flex-1 p-5 md:p-8">
            @yield('content')
        </main>

        <footer class="px-8 py-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between flex-wrap gap-3">
            <div class="text-xs text-slate-400 font-mono">{{ config('app.name') }} Admin · v1.0</div>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Landing Page</a>
                <span class="text-xs text-slate-300 dark:text-slate-600">·</span>
                <span class="text-xs text-slate-400">{{ now()->format('Y') }}</span>
            </div>
        </footer>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-2 pointer-events-none"></div>

    <script>
    function isDark() { return document.documentElement.classList.contains('dark'); }

    function toggleDark() {
        document.documentElement.classList.toggle('dark');
        document.getElementById('iconMoon').classList.toggle('hidden');
        document.getElementById('iconSun').classList.toggle('hidden');
        localStorage.setItem('theme', isDark() ? 'dark' : 'light');
    }

    // Sync theme toggle icons with current theme state (class already set in <head>)
    (function syncThemeIcons() {
        if (document.documentElement.classList.contains('dark')) {
            const moon = document.getElementById('iconMoon');
            const sun  = document.getElementById('iconSun');
            if (moon) moon.classList.add('hidden');
            if (sun)  sun.classList.remove('hidden');
        }
    })();

    function toggleSidebar() {
        const s = document.getElementById('sidebar');
        const o = document.getElementById('sidebarOverlay');
        const isOpen = !s.classList.contains('-translate-x-full');
        s.classList.toggle('-translate-x-full', isOpen);
        o.classList.toggle('hidden', isOpen);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) document.getElementById('sidebarOverlay').classList.add('hidden');
    });

    let sidebarCollapsed = false;

    function applyCollapseState(collapsed) {
        const sidebar     = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('mainWrapper');
        sidebar.classList.toggle('collapsed', collapsed);
        mainWrapper.classList.toggle('sidebar-collapsed', collapsed);
        document.body.classList.toggle('sidebar-is-collapsed', collapsed);
    }

    function toggleCollapse() {
        sidebarCollapsed = !sidebarCollapsed;
        applyCollapseState(sidebarCollapsed);
        localStorage.setItem('sidebarCollapsed', sidebarCollapsed ? '1' : '0');
    }

    (function initCollapse() {
        if (localStorage.getItem('sidebarCollapsed') === '1') {
            sidebarCollapsed = true;
            applyCollapseState(true);
        }
    })();

    document.querySelectorAll('.sidebar-link[data-href]').forEach(link => {
        const href = link.getAttribute('data-href');
        if (href && window.location.pathname.startsWith(href)) {
            link.classList.add('active');
        }
        link.addEventListener('click', function () {
            if (window.innerWidth < 1024) toggleSidebar();
        });
    });

    document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            if (sidebarCollapsed) toggleCollapse();
            this.classList.toggle('dropdown-open');
        });
    });

    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('mouseenter', function () {
            const tooltip = this.querySelector('.sidebar-tooltip');
            if (!tooltip) return;
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top + rect.height / 2) + 'px';
        });
    });

    function toggleDropdown() {
        document.getElementById('profileDropdown').classList.toggle('hidden');
    }

    function toggleNotif() {
        document.getElementById('notifPanel').classList.toggle('open');
    }

    document.addEventListener('click', e => {
        const profileWrapper = document.getElementById('profileDropdownWrapper');
        const notifWrapper   = document.getElementById('notifWrapper');
        if (profileWrapper && !profileWrapper.contains(e.target)) {
            document.getElementById('profileDropdown')?.classList.add('hidden');
        }
        if (notifWrapper && !notifWrapper.contains(e.target)) {
            document.getElementById('notifPanel')?.classList.remove('open');
        }
    });
    </script>

    @stack('modals')

    <x-allert.allert />

    @stack('scripts')
</body>
</html>
