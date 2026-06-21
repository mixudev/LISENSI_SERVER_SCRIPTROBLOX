@php
    $user    = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 2));
    $navMain = [
        ['href' => route('admin.dashboard'),        'path' => '/admin/dashboard',        'label' => 'Dashboard',  'icon' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>'],
        ['href' => route('admin.licenses.index'),   'path' => '/admin/licenses',         'label' => 'Lisensi',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>'],
        ['href' => route('admin.products.index'),   'path' => '/admin/products',         'label' => 'Produk',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        ['href' => route('admin.users.index'),      'path' => '/admin/users',            'label' => 'Pengguna',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
    ];
    $navSecurity = [
        ['href' => route('admin.api-logs.index'),   'path' => '/admin/api-logs',         'label' => 'Log API',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
        ['href' => route('admin.activities.index'), 'path' => '/admin/activities',       'label' => 'Aktivitas',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['href' => route('admin.inject-test.index'),'path' => '/admin/inject-test',      'label' => 'Test Inject',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ];
    $navDiscord = [
        ['href' => route('admin.discord-admins.index'), 'path' => '/admin/discord-admins', 'label' => 'Admin Discord', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
        ['href' => route('admin.ai-keys.index'),         'path' => '/admin/ai-keys',         'label' => 'AI Bot Settings', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>'],
        ['href' => route('admin.reminders.index'),       'path' => '/admin/reminders',       'label' => 'Reminders',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />'],
        ['href' => route('admin.playlists.index'),       'path' => '/admin/playlists',       'label' => 'Playlists',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2Zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2ZM9 10l12-3"/>'],
    ];
@endphp

<aside id="sidebar"
    class="fixed top-0 left-0 h-full bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 z-40 flex flex-col -translate-x-full lg:translate-x-0">

    {{-- ── LOGO ── --}}
    <div class="border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
        <div id="sidebar-logo-area" class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/30">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div id="logo-text" class="overflow-hidden">
                <div class="text-[15px] font-semibold tracking-tight text-slate-900 dark:text-white whitespace-nowrap">{{ config('app.name') }}</div>
                <div class="text-[10px] font-mono text-slate-400 uppercase tracking-widest whitespace-nowrap">Admin Panel</div>
            </div>
        </div>
    </div>

    {{-- ── NAV ── --}}
    <nav class="flex-1 py-4 overflow-y-auto space-y-5 px-3" id="sidebarNav">

        {{-- Main --}}
        <div>
            <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">Main</p>
            <ul class="space-y-0.5">
                @foreach ($navMain as $item)
                    @php $isActive = request()->is(ltrim($item['path'], '/') . '*'); @endphp
                    <li>
                        <a href="{{ $item['href'] }}"
                            data-href="{{ $item['path'] }}"
                            class="sidebar-link {{ $isActive ? 'active' : '' }}"
                            aria-label="{{ $item['label'] }}">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                                    {!! $item['icon'] !!}
                                </svg>
                            </span>
                            <span class="sidebar-label">{{ $item['label'] }}</span>
                            <span class="sidebar-tooltip">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Security --}}
        <div>
            <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">Monitoring</p>
            <ul class="space-y-0.5">
                @foreach ($navSecurity as $item)
                    @php $isActive = request()->is(ltrim($item['path'], '/') . '*'); @endphp
                    <li>
                        <a href="{{ $item['href'] }}"
                            data-href="{{ $item['path'] }}"
                            class="sidebar-link {{ $isActive ? 'active' : '' }}"
                            aria-label="{{ $item['label'] }}">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                                    {!! $item['icon'] !!}
                                </svg>
                            </span>
                            <span class="sidebar-label">{{ $item['label'] }}</span>
                            <span class="sidebar-tooltip">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Discord Bot --}}
        <div>
            <p class="sidebar-section-title px-3 mb-1.5 text-[10px] font-mono font-medium uppercase tracking-widest text-slate-400">Discord Bot</p>
            <ul class="space-y-0.5">
                @foreach ($navDiscord as $item)
                    @php $isActive = request()->is(ltrim($item['path'], '/') . '*'); @endphp
                    <li>
                        <a href="{{ $item['href'] }}"
                            data-href="{{ $item['path'] }}"
                            class="sidebar-link {{ $isActive ? 'active' : '' }}"
                            aria-label="{{ $item['label'] }}">
                            <span class="sidebar-icon w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-4 h-4">
                                    {!! $item['icon'] !!}
                                </svg>
                            </span>
                            <span class="sidebar-label">{{ $item['label'] }}</span>
                            <span class="sidebar-tooltip">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

    </nav>


</aside>
