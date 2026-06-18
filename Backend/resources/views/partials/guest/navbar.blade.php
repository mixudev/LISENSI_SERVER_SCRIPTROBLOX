<nav id="navbar" class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 no-underline">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-indigo-800">
                <i class="ti ti-key text-base text-white" aria-hidden="true"></i>
            </div>
            <span class="text-base font-semibold tracking-tight text-white">{{ config('app.name') }}</span>
        </a>

        <div class="hidden items-center gap-8 md:flex">
            <a href="{{ route('home') }}#hero" class="text-sm text-white/55 no-underline transition hover:text-white">Beranda</a>
            <a href="{{ route('home') }}#products" class="text-sm text-white/55 no-underline transition hover:text-white">Produk</a>
            <a href="{{ route('home') }}#features" class="text-sm text-white/55 no-underline transition hover:text-white">Fitur</a>
            <a href="{{ route('home') }}#how" class="text-sm text-white/55 no-underline transition hover:text-white">Cara Kerja</a>
            <a href="{{ route('home') }}#faq" class="text-sm text-white/55 no-underline transition hover:text-white">FAQ</a>
        </div>

        <div class="hidden items-center gap-2.5 md:flex">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-white/20 px-5 py-2.5 text-sm text-white/75 no-underline transition hover:border-violet-400/50 hover:bg-violet-500/10 hover:text-white">Masuk</a>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 px-5 py-2.5 text-sm font-medium text-white no-underline shadow-lg shadow-violet-900/30 transition hover:-translate-y-px hover:from-violet-500 hover:to-violet-600">Daftar Gratis</a>
        </div>

        <button type="button" id="menuBtn" class="flex items-center border-0 bg-transparent text-xl text-white md:hidden" aria-label="Buka menu">
            <i class="ti ti-menu-2" aria-hidden="true"></i>
        </button>
    </div>
</nav>

<div id="mobileMenu" class="fixed inset-0 z-[99] hidden flex-col items-center justify-center gap-8 bg-[#050508]/97">
    <button type="button" id="closeMenu" class="absolute top-5 right-6 border-0 bg-transparent text-xl text-white" aria-label="Tutup menu">
        <i class="ti ti-x" aria-hidden="true"></i>
    </button>
    <a href="{{ route('home') }}#hero" class="text-xl text-white/55 no-underline" onclick="closeMobileMenu()">Beranda</a>
    <a href="{{ route('home') }}#products" class="text-xl text-white/55 no-underline" onclick="closeMobileMenu()">Produk</a>
    <a href="{{ route('home') }}#features" class="text-xl text-white/55 no-underline" onclick="closeMobileMenu()">Fitur</a>
    <a href="{{ route('home') }}#how" class="text-xl text-white/55 no-underline" onclick="closeMobileMenu()">Cara Kerja</a>
    <a href="{{ route('home') }}#faq" class="text-xl text-white/55 no-underline" onclick="closeMobileMenu()">FAQ</a>
    <div class="mt-4 flex w-52 flex-col gap-3">
        <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-white/20 px-5 py-2.5 text-sm text-white/75 no-underline">Masuk</a>
        <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 px-5 py-2.5 text-sm font-medium text-white no-underline">Daftar Gratis</a>
    </div>
</div>
