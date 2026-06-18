<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — License Server</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased" x-data="{ mobileOpen: false, faq: null }">

    {{-- ── NAVBAR ─────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="#" class="flex items-center gap-2 font-bold text-xl text-gray-900">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                {{ config('app.name') }}
            </a>

            <nav class="hidden md:flex items-center gap-1">
                @foreach (['#hero' => 'Beranda', '#features' => 'Fitur', '#pricing' => 'Produk', '#how' => 'Cara Kerja', '#contact' => 'Kontak'] as $href => $label)
                    <a href="{{ $href }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="hidden sm:block px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:border-blue-400 hover:text-blue-600 transition-colors">
                    Login
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Daftar
                </a>
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-cloak class="md:hidden border-t border-gray-100 px-4 py-3 flex flex-col gap-1">
            @foreach (['#hero' => 'Beranda', '#features' => 'Fitur', '#pricing' => 'Produk', '#how' => 'Cara Kerja', '#contact' => 'Kontak'] as $href => $label)
                <a href="{{ $href }}" @click="mobileOpen = false" class="px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">{{ $label }}</a>
            @endforeach
        </div>
    </header>

    {{-- ── HERO ────────────────────────────────────────── --}}
    <section id="hero" class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-blue-500/20 border border-blue-500/30 text-blue-300 text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                License Server v1.0
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-5">
                Proteksi Produk Anda<br>
                <span class="text-blue-400">dengan License Management</span><br>
                yang Andal & Cepat
            </h1>
            <p class="text-base sm:text-lg text-slate-300 mb-8 max-w-2xl mx-auto">
                Generate, validasi, dan kelola lisensi dengan HWID binding. Response API &lt;200ms dengan Redis Cache.
                Satu dashboard untuk semua produk Anda.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#pricing" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-colors">
                    Lihat Produk
                </a>
                <a href="#features" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl border border-white/20 transition-colors">
                    Dokumentasi API
                </a>
            </div>
        </div>
    </section>

    {{-- ── FITUR ───────────────────────────────────────── --}}
    <section id="features" class="py-20 px-4 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Fitur Unggulan</h2>
                <p class="text-gray-500">Semua yang Anda butuhkan untuk melindungi produk Anda</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ([
                    ['icon' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', 'title' => 'Proteksi HWID', 'desc' => 'Lisensi terikat ke satu perangkat berdasarkan Hardware ID. Tidak bisa dibagikan atau digunakan di perangkat lain.'],
                    ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10', 'title' => 'Multi Produk', 'desc' => 'Kelola banyak produk dari satu dashboard. Setiap produk memiliki konfigurasi lisensi dan HWID reset yang berbeda.'],
                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'API Cepat', 'desc' => 'Response time < 200ms dengan Redis Cache. Mendukung 500+ request per detik untuk ribuan pengguna bersamaan.'],
                    ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'title' => 'Reset HWID', 'desc' => 'Pengguna dapat pindah perangkat sesuai batas yang ditentukan per produk, dengan cooldown yang bisa dikonfigurasi.'],
                    ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Dashboard Lengkap', 'desc' => 'Panel admin terpisah dari dashboard user. Admin bisa generate, suspend, ban lisensi. User bisa lihat dan download produk.'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Audit Log', 'desc' => 'Semua aktivitas user dan request API tercatat rinci lengkap dengan IP, timestamp, dan response time.'],
                ] as $feature)
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── HARGA ───────────────────────────────────────── --}}
    <section id="pricing" class="py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Pilih Paket</h2>
                <p class="text-gray-500">Lisensi yang sesuai untuk setiap kebutuhan</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse ($products as $index => $product)
                    @php $isPopular = $index === 1; @endphp
                    <div class="relative rounded-2xl border-2 p-6 flex flex-col {{ $isPopular ? 'border-blue-500 shadow-xl shadow-blue-100' : 'border-gray-200 shadow-sm' }}">
                        @if ($isPopular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">Populer</span>
                            </div>
                        @endif

                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $product->description }}</p>

                        <div class="mb-4">
                            <span class="text-3xl font-extrabold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <span class="text-sm text-gray-400">/ lisensi</span>
                        </div>

                        <ul class="flex flex-col gap-2 mb-6 flex-1">
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Durasi {{ $product->license_duration_days }} hari
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Reset HWID {{ $product->max_hwid_resets }}x
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Interval reset {{ $product->hwid_reset_interval_days }} hari
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Dashboard User
                            </li>
                        </ul>

                        <a href="#contact"
                            class="block text-center py-2.5 rounded-xl font-semibold text-sm transition-colors
                                {{ $isPopular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'border-2 border-gray-200 text-gray-700 hover:border-blue-400 hover:text-blue-600' }}">
                            Beli Sekarang
                        </a>
                    </div>
                @empty
                    <p class="col-span-4 text-center text-gray-400 py-12">Produk belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ── CARA KERJA ─────────────────────────────────── --}}
    <section id="how" class="py-20 px-4 bg-gray-50">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Cara Kerja</h2>
                <p class="text-gray-500">Proses sederhana dari beli hingga aktif</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-center">
                @foreach ([
                    ['num' => 1, 'title' => 'Beli Lisensi', 'desc' => 'Pilih paket yang sesuai dan dapatkan License Key dari admin'],
                    ['num' => 2, 'title' => 'Login Dashboard', 'desc' => 'Masuk ke Dashboard User untuk melihat lisensi Anda'],
                    ['num' => 3, 'title' => 'Pasang di Aplikasi', 'desc' => 'Masukkan License Key pada aplikasi atau script Anda'],
                    ['num' => 4, 'title' => 'HWID Terikat', 'desc' => 'Saat pertama digunakan, perangkat Anda otomatis terdaftar'],
                    ['num' => 5, 'title' => 'Aplikasi Aktif', 'desc' => 'Gunakan aplikasi selama lisensi masih berlaku'],
                ] as $i => $step)
                    @if ($i > 0)
                        <div class="hidden sm:flex justify-center">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    @endif
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 text-center">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-lg font-bold mx-auto mb-3">
                            {{ $step['num'] }}
                        </div>
                        <h3 class="font-bold text-gray-900 mb-1 text-sm">{{ $step['title'] }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── STATISTIK ───────────────────────────────────── --}}
    <section class="py-16 px-4 bg-blue-600 text-white">
        <div class="max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
            @foreach ([
                ['value' => number_format($stats['active_licenses']), 'label' => 'Lisensi Aktif'],
                ['value' => number_format($stats['total_users']),     'label' => 'Pengguna'],
                ['value' => $stats['uptime'],                         'label' => 'Uptime API'],
                ['value' => number_format($stats['total_products']),  'label' => 'Produk'],
            ] as $stat)
                <div>
                    <p class="text-3xl sm:text-4xl font-extrabold mb-1">{{ $stat['value'] }}</p>
                    <p class="text-sm text-blue-200">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── FAQ ─────────────────────────────────────────── --}}
    <section class="py-20 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">FAQ</h2>
            </div>

            <div class="flex flex-col gap-3">
                @foreach ([
                    ['q' => 'Apa itu HWID?', 'a' => 'HWID (Hardware ID) adalah pengenal unik perangkat Anda. Sistem menggunakan HWID untuk memastikan satu lisensi hanya bisa digunakan di satu perangkat.'],
                    ['q' => 'Berapa kali saya bisa reset HWID?', 'a' => 'Jumlah reset HWID tergantung paket yang Anda beli. Setiap paket memiliki batas reset dan interval waktu yang berbeda.'],
                    ['q' => 'Apakah lisensi bisa digunakan di banyak PC?', 'a' => 'Tidak. Satu lisensi hanya bisa digunakan di satu perangkat sekaligus. Untuk pindah perangkat, gunakan fitur Reset HWID.'],
                    ['q' => 'Bagaimana cara perpanjang lisensi?', 'a' => 'Login ke Dashboard User, lalu klik tombol Perpanjang pada lisensi yang diinginkan untuk menghubungi admin.'],
                    ['q' => 'Apakah ada refund?', 'a' => 'Kebijakan refund ditentukan oleh admin. Silakan hubungi kami melalui kontak yang tersedia untuk informasi lebih lanjut.'],
                ] as $index => $item)
                    <div x-data class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <button @click="faq === {{ $index }} ? faq = null : faq = {{ $index }}"
                            class="w-full px-5 py-4 text-left flex items-center justify-between gap-4">
                            <span class="font-semibold text-gray-900 text-sm">{{ $item['q'] }}</span>
                            <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform" :class="faq === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="faq === {{ $index }}" x-cloak class="px-5 pb-4 text-sm text-gray-600 leading-relaxed">
                            {{ $item['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── FOOTER ──────────────────────────────────────── --}}
    <footer id="contact" class="bg-slate-900 text-white py-16 px-4">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">
            <div>
                <div class="flex items-center gap-2 font-bold text-lg mb-3">
                    <div class="w-7 h-7 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    {{ config('app.name') }}
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Platform license management yang andal untuk melindungi produk digital Anda.
                </p>
            </div>
            <div>
                <h4 class="font-semibold mb-3 text-sm">Link Cepat</h4>
                <div class="flex flex-col gap-2">
                    @foreach (['#hero' => 'Beranda', '#features' => 'Fitur', '#pricing' => 'Produk', '#contact' => 'Kontak'] as $href => $label)
                        <a href="{{ $href }}" class="text-sm text-slate-400 hover:text-white transition-colors">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-3 text-sm">Kontak</h4>
                <div class="flex flex-col gap-2 text-sm text-slate-400">
                    <p>Email: admin@example.com</p>
                    <p>WhatsApp: (tersedia di panel admin)</p>
                </div>
            </div>
        </div>
        <div class="max-w-6xl mx-auto mt-10 pt-6 border-t border-slate-800 text-xs text-slate-500 flex flex-wrap justify-between gap-2">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-white transition-colors">Syarat Penggunaan</a>
            </div>
        </div>
    </footer>

</body>
</html>
