<x-layouts.guest title="Syarat & Ketentuan">
    {{-- Hero --}}
    <section class="relative border-b border-white/10 px-6 py-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle,rgba(139,120,255,0.08)_1px,transparent_1px)] bg-size-[32px_32px] opacity-40"></div>
        <div class="pointer-events-none absolute -top-20 left-1/2 h-[400px] w-[400px] -translate-x-1/2 rounded-full bg-indigo-600/8 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl text-center">
            <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-violet-500/25 bg-violet-500/10 px-3 py-1 text-xs text-violet-300">
                <i class="ti ti-file-description" aria-hidden="true"></i>
                Dokumen Legal
            </div>
            <h1 class="mb-4 text-4xl font-bold tracking-tight text-white">Syarat &amp; Ketentuan</h1>
            <p class="text-sm text-white/40">
                Terakhir diperbarui: <time datetime="{{ now()->format('Y-m-d') }}">{{ now()->translatedFormat('d F Y') }}</time>
            </p>
        </div>
    </section>

    {{-- Content --}}
    <section class="px-6 py-16">
        <div class="mx-auto max-w-3xl">

            {{-- Nav top --}}
            <div class="mb-10 flex items-center gap-2 text-sm text-white/40">
                <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                <i class="ti ti-chevron-right text-xs"></i>
                <span class="text-white/70">Syarat &amp; Ketentuan</span>
            </div>

            {{-- Notice box --}}
            <div class="mb-10 rounded-xl border border-amber-500/20 bg-amber-500/8 px-6 py-5">
                <p class="text-sm leading-relaxed text-amber-200">
                    <strong class="font-semibold">Penting:</strong>
                    Dengan menggunakan layanan {{ config('app.name') }}, Anda menyetujui syarat dan ketentuan
                    di bawah ini. Harap baca dengan seksama sebelum menggunakan layanan kami.
                </p>
            </div>

            @php
                $sections = [
                    [
                        'icon' => 'ti-info-circle',
                        'title' => '1. Penerimaan Syarat',
                        'content' => [
                            'Dengan mengakses atau menggunakan layanan {{ config(\'app.name\') }}, Anda menyatakan bahwa Anda telah membaca, memahami, dan menyetujui Syarat & Ketentuan ini.',
                            'Jika Anda tidak menyetujui syarat ini, harap hentikan penggunaan layanan kami.',
                            'Syarat ini berlaku untuk semua pengguna, termasuk pengunjung, pengguna terdaftar, dan pemegang lisensi.',
                            'Kami berhak mengubah syarat ini sewaktu-waktu. Perubahan akan diberitahukan dan berlaku sejak dipublikasikan.',
                        ],
                    ],
                    [
                        'icon' => 'ti-key',
                        'title' => '2. Lisensi & Penggunaan',
                        'content' => [
                            'Setiap lisensi yang diterbitkan adalah <strong>non-transferable</strong> — tidak dapat dipindahtangankan kepada pihak lain.',
                            'Satu lisensi hanya berlaku untuk satu perangkat (HWID) dalam satu waktu.',
                            'Dilarang keras berbagi, mendistribusikan, atau menjual kembali lisensi yang Anda miliki tanpa izin tertulis.',
                            'Dilarang memodifikasi, mendekompilasi, atau melakukan reverse-engineering terhadap perangkat lunak yang dilindungi lisensi.',
                            'Kami berhak mencabut lisensi tanpa pemberitahuan jika ditemukan pelanggaran syarat ini.',
                        ],
                    ],
                    [
                        'icon' => 'ti-brand-roblox',
                        'title' => '3. Integrasi Roblox OAuth',
                        'content' => [
                            'Layanan ini terintegrasi dengan Roblox OAuth 2.0 untuk verifikasi identitas pengguna.',
                            'Anda harus memiliki akun Roblox yang valid untuk menggunakan fitur yang memerlukan verifikasi Roblox.',
                            'Kami tidak berafiliasi, disponsori, atau diendorsasi oleh Roblox Corporation.',
                            'Penggunaan data Roblox Anda tunduk pada Kebijakan Privasi kami dan Ketentuan Layanan Roblox.',
                            'Anda bertanggung jawab atas keamanan akun Roblox Anda.',
                        ],
                    ],
                    [
                        'icon' => 'ti-credit-card',
                        'title' => '4. Pembayaran & Pengembalian Dana',
                        'content' => [
                            'Semua harga ditampilkan dalam Rupiah (IDR) dan sudah termasuk pajak yang berlaku.',
                            'Pembayaran diproses melalui Midtrans dengan metode QRIS dan lainnya.',
                            'Lisensi aktif setelah pembayaran dikonfirmasi secara otomatis oleh sistem.',
                            '<strong>Kebijakan Pengembalian Dana:</strong> Pengembalian dana dapat dipertimbangkan dalam 24 jam setelah pembelian jika terdapat masalah teknis yang tidak dapat diselesaikan. Hubungi admin untuk mengajukan klaim.',
                            'Pengembalian dana tidak berlaku untuk lisensi yang telah diaktifkan dan digunakan.',
                        ],
                    ],
                    [
                        'icon' => 'ti-shield-x',
                        'title' => '5. Penggunaan yang Dilarang',
                        'content' => [
                            'Menggunakan layanan untuk aktivitas ilegal atau melanggar hukum yang berlaku.',
                            'Mencoba meretas, mengeksploitasi, atau mengganggu sistem layanan kami.',
                            'Membuat akun palsu atau menggunakan informasi identitas orang lain.',
                            'Menggunakan bot atau alat otomatis untuk mengakses layanan tanpa izin.',
                            'Mendistribusikan malware, virus, atau kode berbahaya melalui platform kami.',
                            'Melanggar hak kekayaan intelektual kami atau pihak ketiga manapun.',
                        ],
                    ],
                    [
                        'icon' => 'ti-clock-off',
                        'title' => '6. Masa Aktif & Perpanjangan',
                        'content' => [
                            'Masa aktif lisensi ditentukan saat pembelian sesuai paket yang dipilih.',
                            'Lisensi yang kadaluarsa tidak akan otomatis diperpanjang.',
                            'Kami tidak bertanggung jawab atas kerugian yang timbul akibat lisensi kadaluarsa.',
                            'Perpanjangan lisensi harus dilakukan melalui saluran resmi yang tersedia.',
                        ],
                    ],
                    [
                        'icon' => 'ti-alert-triangle',
                        'title' => '7. Batasan Tanggung Jawab',
                        'content' => [
                            'Layanan disediakan "sebagaimana adanya" tanpa garansi apapun, baik tersurat maupun tersirat.',
                            'Kami tidak bertanggung jawab atas kerusakan tidak langsung, insidental, atau konsekuensial.',
                            'Kami tidak menjamin ketersediaan layanan 100% tanpa gangguan.',
                            'Total tanggung jawab kami tidak melebihi jumlah yang telah Anda bayarkan dalam 30 hari terakhir.',
                        ],
                    ],
                    [
                        'icon' => 'ti-ban',
                        'title' => '8. Penangguhan & Penghentian',
                        'content' => [
                            'Kami berhak menangguhkan atau menghentikan akun yang melanggar syarat ini tanpa pemberitahuan sebelumnya.',
                            'Pengguna dapat menghentikan penggunaan layanan kapan saja dengan menghapus akun mereka.',
                            'Setelah penghentian, semua lisensi yang terkait akan dinonaktifkan.',
                            'Data akun akan dihapus sesuai kebijakan privasi kami.',
                        ],
                    ],
                    [
                        'icon' => 'ti-world',
                        'title' => '9. Hukum yang Berlaku',
                        'content' => [
                            'Syarat ini diatur oleh hukum yang berlaku di Indonesia.',
                            'Setiap sengketa yang timbul akan diselesaikan melalui jalur musyawarah terlebih dahulu.',
                            'Jika musyawarah tidak tercapai, sengketa akan diselesaikan melalui pengadilan yang berwenang di Indonesia.',
                        ],
                    ],
                    [
                        'icon' => 'ti-message',
                        'title' => '10. Kontak',
                        'content' => [
                            'Untuk pertanyaan, keluhan, atau laporan pelanggaran, silakan hubungi admin kami.',
                            'Respons akan diberikan dalam 1-3 hari kerja.',
                            'Untuk masalah teknis mendesak, gunakan saluran Discord yang tersedia.',
                        ],
                    ],
                ];
            @endphp

            <div class="space-y-8">
                @foreach ($sections as $section)
                    <div class="rounded-2xl border border-white/8 bg-white/[0.02] p-6 transition hover:border-white/12">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-violet-500/20 bg-violet-500/10">
                                <i class="ti {{ $section['icon'] }} text-base text-violet-400" aria-hidden="true"></i>
                            </div>
                            <h2 class="text-base font-semibold text-white">{{ $section['title'] }}</h2>
                        </div>
                        <ul class="space-y-2.5">
                            @foreach ($section['content'] as $item)
                                <li class="flex items-start gap-2.5 text-sm leading-relaxed text-white/55">
                                    <i class="ti ti-point-filled mt-1 shrink-0 text-[10px] text-violet-500/60" aria-hidden="true"></i>
                                    <span>{!! $item !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Acceptance --}}
            <div class="mt-10 rounded-xl border border-emerald-500/20 bg-emerald-500/8 px-6 py-5 text-center">
                <i class="ti ti-circle-check mb-2 block text-2xl text-emerald-400" aria-hidden="true"></i>
                <p class="text-sm leading-relaxed text-emerald-200">
                    Dengan mendaftar dan menggunakan layanan {{ config('app.name') }}, Anda menyatakan
                    telah membaca dan menyetujui seluruh Syarat &amp; Ketentuan di atas.
                </p>
            </div>

            {{-- Contact --}}
            <div class="mt-6 rounded-xl border border-white/10 bg-white/[0.02] px-6 py-6 text-center">
                <p class="mb-3 text-sm text-white/50">Ada pertanyaan tentang syarat ini?</p>
                <a href="mailto:admin@{{ parse_url(config('app.url'), PHP_URL_HOST) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-violet-500/30 bg-violet-500/10 px-5 py-2.5 text-sm text-violet-300 transition hover:bg-violet-500/20">
                    <i class="ti ti-mail" aria-hidden="true"></i>
                    Hubungi Admin
                </a>
            </div>

            {{-- Footer nav --}}
            <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-8 text-sm text-white/35">
                <a href="{{ route('privacy') }}" class="flex items-center gap-1.5 transition hover:text-white">
                    <i class="ti ti-arrow-left text-xs"></i> Kebijakan Privasi
                </a>
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 transition hover:text-white">
                    Kembali ke Beranda <i class="ti ti-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>
</x-layouts.guest>
