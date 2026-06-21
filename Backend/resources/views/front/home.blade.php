@php
    $featuredIndex = $products->count() > 1 ? (int) floor(($products->count() - 1) / 2) : 0;

    $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 px-6 py-3 text-sm font-medium text-white no-underline shadow-lg shadow-violet-900/30 transition hover:-translate-y-px hover:from-violet-500 hover:to-violet-600';
    $btnOutline = 'inline-flex items-center justify-center gap-2 rounded-lg border border-white/20 px-6 py-3 text-sm text-white/75 no-underline transition hover:border-violet-400/50 hover:bg-violet-500/10 hover:text-white';
    $glassCard = 'rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-md transition hover:-translate-y-0.5 hover:border-violet-400/30 hover:bg-white/[0.05]';
    $sectionLabel = 'mb-3 text-xs uppercase tracking-[0.12em] text-white/35';
    $sectionTitle = 'text-3xl font-semibold tracking-tight text-white sm:text-4xl';
    $textGradient = 'bg-gradient-to-br from-white via-violet-300 to-violet-500 bg-clip-text text-transparent';
@endphp

<x-layouts.guest title="License Server" :showNavbar="true">
    {{-- HERO --}}
    <section id="hero" class="relative flex min-h-screen items-center overflow-hidden pt-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle,rgba(139,120,255,0.15)_1px,transparent_1px)] bg-size-[32px_32px] opacity-40"></div>
        <div class="pointer-events-none absolute -top-24 -right-20 h-[400px] w-[400px] rounded-full bg-violet-600/10 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 -left-16 h-[300px] w-[300px] rounded-full bg-indigo-700/10 blur-3xl"></div>

        <div class="relative z-10 mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-12 px-6 py-16 lg:flex-row">
            <div class="max-w-2xl">
                <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-violet-500/25 bg-violet-500/10 px-3 py-1 text-xs text-violet-300">
                    <i class="ti ti-shield-check" aria-hidden="true"></i>
                    Sistem Lisensi Kriptografis Generasi Baru
                </div>

                <h1 class="mb-6 text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                    <span class="text-white">Proteksi Software</span><br>
                    <span class="{{ $textGradient }}">Level Enterprise</span>
                </h1>

                <p class="mb-10 max-w-lg text-base leading-relaxed text-white/45 sm:text-lg">
                    Kelola lisensi, binding HWID, validasi real-time, dan dashboard lengkap — semua dalam satu platform yang dirancang untuk performa tinggi.
                </p>

                <div class="mb-14 flex flex-wrap gap-3">
                    <a href="#products" class="{{ $btnPrimary }}">
                        Lihat Produk <i class="ti ti-arrow-right" aria-hidden="true"></i>
                    </a>
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}" class="{{ $btnOutline }}">
                            <i class="ti ti-layout-dashboard" aria-hidden="true"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="{{ $btnOutline }}">
                            <i class="ti ti-rocket" aria-hidden="true"></i> Mulai Gratis
                        </a>
                    @endauth
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <div class="h-7 w-7 rounded-full border-2 border-[#050508] bg-gradient-to-br from-violet-500 to-violet-300"></div>
                            <div class="-ml-2 h-7 w-7 rounded-full border-2 border-[#050508] bg-gradient-to-br from-indigo-700 to-violet-500"></div>
                            <div class="-ml-2 h-7 w-7 rounded-full border-2 border-[#050508] bg-gradient-to-br from-indigo-900 to-violet-600"></div>
                        </div>
                        <span class="text-xs text-white/40">{{ number_format($stats['total_users']) }}+ pengguna terdaftar</span>
                    </div>
                    <div class="flex items-center gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <i class="ti ti-star-filled text-sm text-amber-400" aria-hidden="true"></i>
                        @endfor
                        <span class="ml-1 text-xs text-white/40">Platform terpercaya</span>
                    </div>
                </div>
            </div>

            <div class="hidden shrink-0 animate-float lg:block">
                <div class="{{ $glassCard }} w-72 border-violet-500/30 p-6 shadow-[0_0_20px_rgba(139,120,255,0.08)]">
                    <div class="mb-3 text-[11px] uppercase tracking-widest text-white/35">License Key</div>
                    <div class="rounded-lg border border-violet-500/20 bg-violet-500/10 px-5 py-3 font-mono text-base tracking-widest text-violet-300">LZD-4K7X-9MBR</div>
                    <div class="mt-4 space-y-2 border-t border-white/10 pt-4">
                        <div class="flex justify-between text-xs">
                            <span class="text-white/35">Status</span>
                            <span class="flex items-center gap-1.5 text-emerald-400"><span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Active</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-white/35">HWID</span>
                            <span class="font-mono text-white/60">A3F9B2C1...</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-white/35">Produk</span>
                            <span class="text-white/60">{{ $products->first()?->name ?? 'Universal' }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 rounded-lg border border-violet-500/20 bg-violet-500/10 px-3.5 py-2.5 text-[11px] text-violet-300">
                        <i class="ti ti-check" aria-hidden="true"></i> Validasi berhasil — &lt;200ms
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="border-y border-white/5 px-6 py-8">
        <div class="mx-auto max-w-6xl">
            <div class="grid grid-cols-2 lg:grid-cols-4">
                <div id="stat1" class="border-b border-white/10 px-4 py-6 text-center lg:border-r lg:border-b-0">
                    <div class="text-3xl font-bold lg:text-4xl {{ $textGradient }}" data-target="{{ max($stats['active_licenses'], 1) }}">0</div>
                    <div class="mt-1 text-xs text-white/35">Lisensi Aktif</div>
                </div>
                <div id="stat2" class="border-b border-white/10 px-4 py-6 text-center lg:border-r lg:border-b-0">
                    <div class="text-3xl font-bold lg:text-4xl {{ $textGradient }}" data-target="{{ max($stats['total_users'], 1) }}">0</div>
                    <div class="mt-1 text-xs text-white/35">Pengguna Terdaftar</div>
                </div>
                <div class="border-r border-white/10 px-4 py-6 text-center">
                    <div class="bg-gradient-to-br from-emerald-400 to-green-500 bg-clip-text text-3xl font-bold text-transparent lg:text-4xl">{{ $stats['uptime'] }}</div>
                    <div class="mt-1 text-xs text-white/35">Uptime API</div>
                </div>
                <div class="px-4 py-6 text-center">
                    <div class="text-3xl font-bold lg:text-4xl {{ $textGradient }}">&lt;200ms</div>
                    <div class="mt-1 text-xs text-white/35">Response Time</div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section id="features" class="px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-16 text-center">
                <div class="{{ $sectionLabel }}">Fitur Unggulan</div>
                <h2 class="{{ $sectionTitle }}">Dirancang untuk <span class="{{ $textGradient }}">Keandalan Penuh</span></h2>
                <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-white/45">Setiap fitur dibangun dengan standar enterprise — aman, cepat, dan mudah digunakan.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'ti-cpu', 'title' => 'Proteksi HWID', 'desc' => 'Lisensi terikat ke satu perangkat secara kriptografis. Tidak bisa dibagikan tanpa izin admin.'],
                    ['icon' => 'ti-layers-intersect', 'title' => 'Multi Produk', 'desc' => 'Kelola banyak produk dengan konfigurasi berbeda dari satu dashboard terpusat.'],
                    ['icon' => 'ti-bolt', 'title' => 'API <200ms', 'desc' => 'Validasi lisensi real-time dengan Redis Cache. Respon kilat bahkan di jam sibuk.'],
                    ['icon' => 'ti-refresh', 'title' => 'Reset HWID Terkontrol', 'desc' => 'Pengguna dapat pindah perangkat sesuai batas yang ditentukan admin.'],
                    ['icon' => 'ti-layout-dashboard', 'title' => 'Dual Dashboard', 'desc' => 'Panel admin penuh dan dashboard user mandiri yang terpisah.'],
                    ['icon' => 'ti-clipboard-list', 'title' => 'Audit Log Lengkap', 'desc' => 'Semua aktivitas tercatat — request API, login, reset HWID, hingga inject script.'],
                ] as $feature)
                <div class="{{ $glassCard }} p-7">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-violet-500/20 bg-violet-500/10 text-xl text-violet-400">
                        <i class="ti {{ $feature['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-2 text-base font-medium text-white">{{ $feature['title'] }}</h3>
                    <p class="text-sm leading-relaxed text-white/40">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PRODUCTS --}}
    <section id="products" class="border-t border-white/5 bg-white/[0.01] px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-16 text-center">
                <div class="{{ $sectionLabel }}">Pilih Paket</div>
                <h2 class="{{ $sectionTitle }}">Harga yang <span class="{{ $textGradient }}">Transparan</span></h2>
                <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-white/45">Mulai dengan paket yang sesuai kebutuhan. Hubungi admin untuk pembelian.</p>
            </div>

            @if ($products->isEmpty())
                <div class="{{ $glassCard }} py-16 text-center">
                    <i class="ti ti-package mb-4 block text-4xl text-white/20" aria-hidden="true"></i>
                    <p class="text-sm text-white/40">Belum ada produk aktif. Silakan hubungi admin.</p>
                </div>
            @else
                @php
                    $productCols = match (min($products->count(), 4)) {
                        1 => 'xl:grid-cols-1',
                        2 => 'xl:grid-cols-2',
                        3 => 'xl:grid-cols-3',
                        default => 'xl:grid-cols-4',
                    };
                @endphp
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $productCols }}">
                    @foreach ($products as $index => $product)
                        @php
                            $isFeatured = $index === $featuredIndex && $products->count() >= 2;
                            $priceLabel = $product->price
                                ? 'Rp ' . ($product->price >= 1000 ? number_format($product->price / 1000, 0, ',', '.') . 'K' : number_format($product->price, 0, ',', '.'))
                                : 'Hubungi Admin';
                            $accessLabel = ($product->access_level ?? 'user') === 'admin' ? 'Admin only' : 'User';
                            $placeLabel = $product->place_ids ? count($product->place_ids) . ' map spesifik' : 'Universal (semua map)';
                        @endphp
                        <div @class([
                            'rounded-2xl border p-7 transition hover:-translate-y-1',
                            'border-violet-500/40 bg-violet-500/10 shadow-[0_0_40px_rgba(107,87,255,0.1)]' => $isFeatured,
                            'border-white/10 bg-white/[0.025] hover:border-violet-400/25 hover:bg-white/[0.04]' => ! $isFeatured,
                        ])>
                            <div class="mb-3 flex items-center justify-between">
                                <div class="text-xs uppercase tracking-widest text-white/35">{{ $product->name }}</div>
                                @if ($isFeatured)
                                    <span class="rounded-full bg-gradient-to-br from-amber-400 to-yellow-300 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-950">Populer</span>
                                @endif
                            </div>
                            <div @class(['mb-1 text-3xl font-bold tracking-tight', $textGradient => ! $product->price, 'text-white' => (bool) $product->price])>{{ $priceLabel }}</div>
                            <div class="mb-7 text-xs text-white/30">v{{ $product->version }} · {{ $accessLabel }}</div>

                            @foreach (['Proteksi HWID', $placeLabel, 'Inject otomatis per map', 'Dashboard User'] as $item)
                                <div class="mb-2.5 flex items-start gap-2.5 text-sm text-white/65">
                                    <i class="ti ti-check mt-0.5 shrink-0 text-violet-500" aria-hidden="true"></i>{{ $item }}
                                </div>
                            @endforeach
                            @if ($product->notes)
                                <div class="mb-2.5 flex items-start gap-2.5 text-sm text-white/65">
                                    <i class="ti ti-check mt-0.5 shrink-0 text-violet-500" aria-hidden="true"></i>{{ \Illuminate\Support\Str::limit($product->notes, 40) }}
                                </div>
                            @endif

                            @auth
                                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}" class="mt-6 w-full {{ $isFeatured ? $btnPrimary : $btnOutline }}">Ke Dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="mt-6 w-full {{ $isFeatured ? $btnPrimary : $btnOutline }}">Daftar Sekarang</a>
                            @endauth
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="how" class="px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-16 text-center">
                <div class="{{ $sectionLabel }}">Cara Kerja</div>
                <h2 class="{{ $sectionTitle }}">Mulai dalam <span class="{{ $textGradient }}">5 Langkah</span></h2>
            </div>

            @php
                $steps = [
                    ['title' => 'Beli Lisensi', 'desc' => 'Pilih paket dan dapatkan License Key unik format LZD-XXXX'],
                    ['title' => 'Login Dashboard', 'desc' => 'Pantau semua lisensi aktif dan status perangkat kamu'],
                    ['title' => 'Input Key di Aplikasi', 'desc' => 'Masukkan License Key pada aplikasi atau script kamu'],
                    ['title' => 'HWID Terikat Otomatis', 'desc' => 'Perangkat terdaftar saat pertama kali aktivasi'],
                    ['title' => 'Aplikasi Berjalan', 'desc' => 'Nikmati akses penuh selama masa lisensi aktif'],
                ];
            @endphp

            <div class="hidden items-start md:flex">
                @foreach ($steps as $i => $step)
                <div class="flex flex-1 flex-col items-center text-center">
                    <div class="mb-6 flex w-full items-center">
                        @if ($i === 0)
                            <div class="h-px flex-1 bg-transparent"></div>
                        @else
                            <div class="h-px flex-1 bg-gradient-to-r from-violet-500/5 to-violet-500/30"></div>
                        @endif
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-violet-500/40 bg-violet-500/15 text-sm font-medium text-violet-400">{{ $i + 1 }}</div>
                        @if ($i < count($steps) - 1)
                            <div class="h-px flex-1 bg-gradient-to-r from-violet-500/30 to-violet-500/5"></div>
                        @else
                            <div class="h-px flex-1 bg-transparent"></div>
                        @endif
                    </div>
                    <div class="px-4">
                        <div class="mb-1.5 text-[15px] font-medium text-white">{{ $step['title'] }}</div>
                        <div class="text-xs leading-relaxed text-white/35">{{ $step['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex flex-col md:hidden">
                @foreach ($steps as $i => $step)
                <div class="flex items-start gap-5 {{ $i < count($steps) - 1 ? 'pb-7' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-violet-500/40 bg-violet-500/15 text-sm font-medium text-violet-400">{{ $i + 1 }}</div>
                        @if ($i < count($steps) - 1)
                            <div class="mt-1 min-h-10 w-px flex-1 bg-violet-500/20"></div>
                        @endif
                    </div>
                    <div class="pt-1">
                        <div class="mb-1 text-[15px] font-medium text-white">{{ $step['title'] }}</div>
                        <div class="text-xs leading-relaxed text-white/35">{{ $step['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-y border-violet-500/10 bg-violet-500/5 px-6 py-16">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-8">
            <div>
                <div class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Siap mulai sekarang?</div>
                <div class="mt-1.5 text-sm text-white/40">Buat akun gratis dan kelola lisensi dari dashboard.</div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="{{ $btnPrimary }}">Daftar Gratis</a>
                <a href="{{ route('login') }}" class="{{ $btnOutline }}">Sudah Punya Akun</a>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="px-6 py-24">
        <div class="mx-auto max-w-2xl">
            <div class="mb-14 text-center">
                <div class="{{ $sectionLabel }}">FAQ</div>
                <h2 class="{{ $sectionTitle }}">Pertanyaan <span class="{{ $textGradient }}">Umum</span></h2>
            </div>
            <div id="faqList" class="divide-y divide-white/10"></div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-white/10 px-6 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="mb-12 grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <a href="{{ route('home') }}" class="mb-4 flex items-center gap-2.5 no-underline">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-indigo-800">
                            <i class="ti ti-key text-base text-white" aria-hidden="true"></i>
                        </div>
                        <span class="text-base font-semibold text-white">{{ config('app.name') }}</span>
                    </a>
                    <p class="max-w-[220px] text-xs leading-relaxed text-white/35">Platform manajemen lisensi dengan HWID binding dan inject script otomatis per map.</p>
                </div>
                <div>
                    <div class="mb-4 text-xs uppercase tracking-widest text-white/25">Navigasi</div>
                    <div class="flex flex-col gap-2.5">
                        @foreach (['#hero' => 'Beranda', '#products' => 'Produk', '#features' => 'Fitur', '#faq' => 'FAQ'] as $href => $label)
                            <a href="{{ $href }}" class="text-sm text-white/55 no-underline transition hover:text-white">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="mb-4 text-xs uppercase tracking-widest text-white/25">Akun</div>
                    <div class="flex flex-col gap-2.5">
                        <a href="{{ route('login') }}" class="text-sm text-white/55 no-underline transition hover:text-white">Masuk</a>
                        <a href="{{ route('register') }}" class="text-sm text-white/55 no-underline transition hover:text-white">Daftar</a>
                        <a href="{{ route('password.request') }}" class="text-sm text-white/55 no-underline transition hover:text-white">Lupa Password</a>
                    </div>
                </div>
                <div>
                    <div class="mb-4 text-xs uppercase tracking-widest text-white/25">Kontak</div>
                    <a href="mailto:admin@{{ parse_url(config('app.url'), PHP_URL_HOST) }}" class="flex items-center gap-2 text-sm text-white/55 no-underline transition hover:text-white">
                        <i class="ti ti-mail text-sm" aria-hidden="true"></i> Email Admin
                    </a>
                </div>
            </div>
            <div class="border-t border-white/10 pt-7">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <span class="text-xs text-white/25">&copy; {{ now()->year }} {{ config('app.name') }}. Hak cipta dilindungi.</span>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('privacy') }}" class="text-xs text-white/25 no-underline transition hover:text-white/60">Kebijakan Privasi</a>
                        <a href="{{ route('terms') }}" class="text-xs text-white/25 no-underline transition hover:text-white/60">Syarat &amp; Ketentuan</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @push('scripts')
    <script>
        const faqs = [
            { q: "Apa itu HWID?", a: "HWID (Hardware ID) adalah pengenal unik perangkat kamu yang dihasilkan dari komponen hardware. Setiap komputer memiliki HWID berbeda, sehingga lisensi hanya bisa aktif di satu perangkat." },
            { q: "Berapa kali saya bisa reset HWID?", a: "Jumlah reset HWID bergantung paket yang kamu pilih. Setiap reset juga memiliki interval minimum antar reset sesuai konfigurasi produk." },
            { q: "Apakah lisensi bisa digunakan di banyak PC?", a: "Tidak. Satu lisensi hanya bisa aktif di satu perangkat dalam waktu bersamaan." },
            { q: "Bagaimana cara perpanjang lisensi?", a: "Login ke Dashboard User, lalu hubungi admin melalui kontak yang tersedia untuk perpanjangan lisensi." },
            { q: "Seberapa cepat API validasi lisensi?", a: "API dirancang untuk merespon dalam waktu kurang dari 200ms berkat Redis Cache." }
        ];

        const faqList = document.getElementById('faqList');
        faqs.forEach((f, i) => {
            faqList.innerHTML += `
                <div>
                    <button type="button" class="flex w-full items-center justify-between gap-4 py-5 text-left text-[15px] text-slate-200" onclick="toggleFaq(${i})">
                        <span>${f.q}</span>
                        <i class="ti ti-plus shrink-0 text-base text-white/30 transition-transform" id="faq-icon-${i}" aria-hidden="true"></i>
                    </button>
                    <div class="max-h-0 overflow-hidden transition-all duration-300" id="faq-${i}">
                        <p class="pb-5 text-sm leading-relaxed text-white/50">${f.a}</p>
                    </div>
                </div>`;
        });

        function toggleFaq(i) {
            document.querySelectorAll('[id^="faq-"]').forEach((el, idx) => {
                if (!el.id.startsWith('faq-icon-') && el.id !== 'faqList') {
                    const num = el.id.replace('faq-', '');
                    if (num !== String(i)) {
                        el.classList.remove('max-h-96');
                        el.classList.add('max-h-0');
                    }
                }
            });
            const content = document.getElementById('faq-' + i);
            const icon = document.getElementById('faq-icon-' + i);
            const isOpen = content.classList.contains('max-h-96');
            content.classList.toggle('max-h-0', isOpen);
            content.classList.toggle('max-h-96', !isOpen);
            icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(45deg)';
        }

        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (navbar) {
                navbar.classList.toggle('bg-[#050508]/85', window.scrollY > 20);
                navbar.classList.toggle('backdrop-blur-xl', window.scrollY > 20);
                navbar.classList.toggle('border-b', window.scrollY > 20);
                navbar.classList.toggle('border-white/10', window.scrollY > 20);
            }
        });

        const mobileMenu = document.getElementById('mobileMenu');
        document.getElementById('menuBtn')?.addEventListener('click', () => mobileMenu.classList.replace('hidden', 'flex'));
        document.getElementById('closeMenu')?.addEventListener('click', () => mobileMenu.classList.replace('flex', 'hidden'));
        function closeMobileMenu() { mobileMenu.classList.replace('flex', 'hidden'); }

        function animateCount(el, target) {
            let start = null;
            const duration = 1800;
            const step = timestamp => {
                if (!start) start = timestamp;
                const progress = Math.min((timestamp - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const value = Math.round(eased * target);
                el.textContent = value >= 1000 ? Math.round(value / 1000) + 'K+' : value.toLocaleString('id-ID') + '+';
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }

        new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const el = e.target.querySelector('[data-target]');
                    if (el && !el.dataset.counted) {
                        el.dataset.counted = true;
                        animateCount(el, parseInt(el.dataset.target));
                    }
                }
            });
        }, { threshold: 0.3 }).observe(document.getElementById('stat1'));
        new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const el = e.target.querySelector('[data-target]');
                    if (el && !el.dataset.counted) {
                        el.dataset.counted = true;
                        animateCount(el, parseInt(el.dataset.target));
                    }
                }
            });
        }, { threshold: 0.3 }).observe(document.getElementById('stat2'));
    </script>
    @endpush
</x-layouts.guest>
