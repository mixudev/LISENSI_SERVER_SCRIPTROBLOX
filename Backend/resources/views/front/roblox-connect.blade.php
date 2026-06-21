<x-layouts.guest title="Kaitkan Akun Roblox" showOrbs="true">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="relative w-full max-w-md border border-white/10 bg-white/5 p-8 backdrop-blur-xl shadow-2xl">
            {{-- Roblox & Discord Logos in Center --}}
            <div class="flex items-center justify-center gap-4 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-600/10 border border-violet-500/20 text-violet-400">
                    <i class="ti ti-brand-discord text-2xl"></i>
                </div>
                <div class="h-1 w-8 bg-gradient-to-r from-violet-500 to-rose-500"></div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-600/10 border border-rose-500/20 text-rose-400">
                    <i class="ti ti-device-gamepad text-2xl"></i>
                </div>
            </div>

            <h1 class="text-center text-xl font-bold tracking-tight text-white mb-2">Kaitkan Akun Roblox</h1>
            <p class="text-center text-xs text-white/50 mb-8 font-medium">Sistem Integrasi Lisensi Bot & Web</p>

            @if (isset($error))
                <div class="mb-6 rounded-lg border border-red-500/20 bg-red-500/5 p-4 text-sm text-red-300">
                    <div class="flex gap-2.5">
                        <i class="ti ti-alert-circle text-lg shrink-0" aria-hidden="true"></i>
                        <div>
                            <p class="font-bold">Gagal Mengaitkan Akun</p>
                            <p class="mt-1 text-xs text-red-400/90 leading-relaxed">{{ $error }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-xs text-white/40">Anda dapat menutup halaman ini dan mencoba kembali dari Discord.</p>
                </div>
            @elseif (isset($success) && $success)
                <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4 text-sm text-emerald-300">
                    <div class="flex gap-2.5">
                        <i class="ti ti-circle-check text-lg shrink-0" aria-hidden="true"></i>
                        <div>
                            <p class="font-bold">Koneksi Berhasil!</p>
                            <p class="mt-1 text-xs text-emerald-400/90 leading-relaxed">
                                Akun Roblox <strong class="text-white">{{ $roblox_username }}</strong> berhasil dikaitkan ke Discord Anda.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center space-y-4">
                    <p class="text-xs text-white/40">Silakan kembali ke Discord. Lisensi Anda sekarang terikat dengan akun Roblox ini.</p>
                    <div class="inline-flex h-8 items-center justify-center rounded-lg bg-emerald-500/10 px-3 text-xs font-semibold text-emerald-400 border border-emerald-500/25">
                        Status: Connected
                    </div>
                </div>
            @else
                {{-- Fallback: Manual Username Form --}}
                <div class="mb-6 rounded-lg border border-amber-500/20 bg-amber-500/5 p-3.5 text-xs text-amber-300 leading-relaxed">
                    <div class="flex gap-2">
                        <i class="ti ti-info-circle text-base shrink-0"></i>
                        <p>Roblox OAuth tidak dikonfigurasi. Harap masukkan username Roblox Anda secara manual untuk mengaitkan akun.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('roblox.manual-submit') }}" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label for="roblox_username" class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-white/60">Username Roblox</label>
                        <input id="roblox_username" type="text" name="roblox_username" required placeholder="Contoh: Builderman"
                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white outline-none placeholder:text-white/25 focus:border-violet-500/50 focus:ring-4 focus:ring-violet-500/15">
                        @error('roblox_username')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                        class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-violet-600 to-violet-700 py-3 text-sm font-medium text-white shadow-lg shadow-violet-900/30 transition hover:-translate-y-px hover:from-violet-500 hover:to-violet-600">
                        Hubungkan Akun
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.guest>
