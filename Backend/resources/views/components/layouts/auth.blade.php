@props(['title' => 'Auth'])

<x-layouts.guest :title="$title" :showOrbs="true">
    <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 no-underline">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-indigo-800">
                        <i class="ti ti-key text-lg text-white" aria-hidden="true"></i>
                    </div>
                    <div class="text-left">
                        <span class="block text-lg font-semibold text-white">{{ config('app.name') }}</span>
                        <span class="block font-mono text-[10px] uppercase tracking-widest text-white/30">License Server</span>
                    </div>
                </a>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-7 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-white/25">
                &copy; {{ now()->year }} {{ config('app.name') }}
            </p>
        </div>
    </div>
</x-layouts.guest>
