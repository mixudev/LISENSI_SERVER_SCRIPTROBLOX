<x-layouts.guest title="Kebijakan Privasi">
    {{-- Hero --}}
    <section class="relative border-b border-white/10 px-6 py-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle,rgba(139,120,255,0.08)_1px,transparent_1px)] bg-size-[32px_32px] opacity-40"></div>
        <div class="pointer-events-none absolute -top-20 left-1/2 h-[400px] w-[400px] -translate-x-1/2 rounded-full bg-violet-600/8 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl text-center">
            <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-violet-500/25 bg-violet-500/10 px-3 py-1 text-xs text-violet-300">
                <i class="ti ti-shield-lock" aria-hidden="true"></i>
                Dokumen Legal
            </div>
            <h1 class="mb-4 text-4xl font-bold tracking-tight text-white">Kebijakan Privasi</h1>
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
                <span class="text-white/70">Kebijakan Privasi</span>
            </div>

            {{-- Notice box --}}
            <div class="mb-10 rounded-xl border border-violet-500/20 bg-violet-500/10 px-6 py-5">
                <p class="text-sm leading-relaxed text-violet-200">
                    <strong class="font-semibold">Ringkasan:</strong>
                    Kami menghargai privasi Anda. Kebijakan ini menjelaskan data apa yang kami kumpulkan,
                    bagaimana kami menggunakannya, dan hak Anda atas data tersebut. Kami tidak menjual
                    data pribadi Anda kepada pihak ketiga.
                </p>
            </div>

            @php
                $sections = [
                    [
                        'icon' => 'ti-database',
                        'title' => '1. Data yang Kami Kumpulkan',
                        'content' => [
                            '<strong>Informasi Akun:</strong> Saat mendaftar, kami mengumpulkan nama pengguna, alamat email, dan password (disimpan dalam bentuk hash terenkripsi).',
                            '<strong>Identitas Roblox:</strong> Saat menghubungkan akun Roblox melalui OAuth 2.0, kami menyimpan Roblox User ID dan username Anda untuk keperluan validasi lisensi.',
                            '<strong>Identitas Discord:</strong> Jika Anda berinteraksi melalui bot Discord kami, kami menyimpan Discord User ID Anda.',
                            '<strong>Data Perangkat (HWID):</strong> Kami mengumpulkan Hardware ID (HWID) perangkat Anda saat pertama kali mengaktifkan lisensi, digunakan semata-mata untuk proteksi lisensi.',
                            '<strong>Log Aktivitas:</strong> Kami mencatat aktivitas seperti validasi lisensi, login, dan reset HWID untuk keamanan dan pemecahan masalah.',
                            '<strong>Data Pembayaran:</strong> Kami tidak menyimpan informasi kartu kredit. Pembayaran diproses melalui Midtrans yang memiliki standar keamanan PCI DSS.',
                        ],
                    ],
                    [
                        'icon' => 'ti-settings',
                        'title' => '2. Cara Kami Menggunakan Data',
                        'content' => [
                            'Memverifikasi identitas dan mengelola akun pengguna.',
                            'Memvalidasi dan mengelola lisensi perangkat lunak Anda.',
                            'Mengirimkan notifikasi terkait akun seperti reset password.',
                            'Mendeteksi dan mencegah penyalahgunaan layanan.',
                            'Memproses transaksi pembayaran melalui penyedia terpercaya.',
                            'Meningkatkan performa dan keandalan layanan kami.',
                        ],
                    ],
                    [
                        'icon' => 'ti-share',
                        'title' => '3. Berbagi Data dengan Pihak Ketiga',
                        'content' => [
                            '<strong>Midtrans:</strong> Pemrosesan pembayaran. Data transaksi dikirim ke Midtrans sesuai kebutuhan pembayaran.',
                            '<strong>Roblox Corporation:</strong> OAuth 2.0 digunakan untuk verifikasi identitas Roblox. Kami hanya menerima data yang Anda setujui untuk dibagikan.',
                            'Kami <strong>tidak menjual, menyewakan, atau memperdagangkan</strong> data pribadi Anda kepada pihak ketiga lainnya.',
                            'Kami dapat mengungkapkan data jika diwajibkan oleh hukum yang berlaku.',
                        ],
                    ],
                    [
                        'icon' => 'ti-lock',
                        'title' => '4. Keamanan Data',
                        'content' => [
                            'Password disimpan menggunakan algoritma bcrypt dengan faktor cost tinggi.',
                            'Komunikasi antara klien dan server dienkripsi menggunakan HTTPS/TLS.',
                            'Akses ke database dibatasi dan dimonitor secara ketat.',
                            'HWID dan data sensitif tidak pernah ditampilkan secara publik.',
                        ],
                    ],
                    [
                        'icon' => 'ti-calendar',
                        'title' => '5. Penyimpanan & Penghapusan Data',
                        'content' => [
                            'Data akun disimpan selama akun aktif atau hingga Anda meminta penghapusan.',
                            'Log aktivitas disimpan maksimal 90 hari untuk keperluan keamanan.',
                            'Untuk meminta penghapusan data, hubungi admin melalui kontak yang tersedia.',
                            'Setelah penghapusan dikonfirmasi, data akan dihapus permanen dalam 30 hari.',
                        ],
                    ],
                    [
                        'icon' => 'ti-user-check',
                        'title' => '6. Hak Pengguna',
                        'content' => [
                            '<strong>Akses:</strong> Anda berhak meminta salinan data pribadi yang kami simpan.',
                            '<strong>Koreksi:</strong> Anda dapat memperbarui informasi akun melalui halaman profil.',
                            '<strong>Penghapusan:</strong> Anda dapat meminta penghapusan akun dan semua data terkait.',
                            '<strong>Portabilitas:</strong> Anda berhak mendapatkan data Anda dalam format yang dapat dibaca mesin.',
                        ],
                    ],
                    [
                        'icon' => 'ti-cookie',
                        'title' => '7. Cookie & Penyimpanan Lokal',
                        'content' => [
                            'Kami menggunakan cookie sesi untuk menjaga status login Anda.',
                            'Cookie CSRF digunakan untuk melindungi formulir dari serangan cross-site request forgery.',
                            'Kami tidak menggunakan cookie pelacak atau iklan pihak ketiga.',
                        ],
                    ],
                    [
                        'icon' => 'ti-refresh',
                        'title' => '8. Perubahan Kebijakan',
                        'content' => [
                            'Kami dapat memperbarui kebijakan ini dari waktu ke waktu.',
                            'Perubahan signifikan akan diberitahukan melalui email atau notifikasi dalam aplikasi.',
                            'Tanggal "Terakhir diperbarui" di atas akan selalu menunjukkan versi terkini.',
                            'Penggunaan layanan setelah perubahan berlaku berarti Anda menyetujui kebijakan baru.',
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

            {{-- Contact --}}
            <div class="mt-10 rounded-xl border border-white/10 bg-white/[0.02] px-6 py-6 text-center">
                <p class="mb-3 text-sm text-white/50">Ada pertanyaan tentang privasi Anda?</p>
                <a href="mailto:admin@{{ parse_url(config('app.url'), PHP_URL_HOST) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-violet-500/30 bg-violet-500/10 px-5 py-2.5 text-sm text-violet-300 transition hover:bg-violet-500/20">
                    <i class="ti ti-mail" aria-hidden="true"></i>
                    Hubungi Admin
                </a>
            </div>

            {{-- Footer nav --}}
            <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-8 text-sm text-white/35">
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 transition hover:text-white">
                    <i class="ti ti-arrow-left text-xs"></i> Kembali ke Beranda
                </a>
                <a href="{{ route('terms') }}" class="flex items-center gap-1.5 transition hover:text-white">
                    Syarat & Ketentuan <i class="ti ti-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>
</x-layouts.guest>
