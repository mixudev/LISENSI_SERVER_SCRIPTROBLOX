<x-dashboard.user.layouts.app title="Lisensi Saya">

    {{-- Filter --}}
    <div class="flex gap-2 mb-6">
        @foreach (['all' => 'Semua', 'active' => 'Aktif', 'expired' => 'Kadaluarsa'] as $val => $label)
            <a href="{{ route('user.licenses.index', ['filter' => $val]) }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                    {{ $filter === $val ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- License Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($filtered as $license)
            @php
                $isExpiringSoon = $license->expired_at?->between(now(), now()->addDays(7));
                $statusBadge    = match($license->status) {
                    'active'    => $license->isExpired() ? 'bg-gray-50 text-gray-600' : 'bg-green-50 text-green-700',
                    'expired'   => 'bg-gray-50 text-gray-600',
                    'banned'    => 'bg-red-50 text-red-700',
                    'suspended' => 'bg-yellow-50 text-yellow-700',
                    default     => 'bg-gray-50 text-gray-600',
                };
                $statusLabel = match($license->status) {
                    'active'    => $license->isExpired() ? 'Kadaluarsa' : 'Aktif',
                    'expired'   => 'Kadaluarsa',
                    'banned'    => 'Dibanned',
                    'suspended' => 'Disuspend',
                    default     => ucfirst($license->status),
                };
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">
                            {{ $license->product?->name }}
                        </span>
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusBadge }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4 flex-1 flex flex-col gap-3">
                    {{-- License Key --}}
                    <div>
                        <p class="text-xs text-gray-500 mb-1">License Key</p>
                        <div class="flex items-center gap-2">
                            <code class="text-sm font-mono font-bold text-gray-900 flex-1 truncate">{{ $license->license_key }}</code>
                            <button
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ $license->license_key }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="text-gray-400 hover:text-blue-600 transition-colors shrink-0">
                                <span x-show="!copied">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <span x-show="copied" class="text-green-600 text-xs">✓</span>
                            </button>
                        </div>
                    </div>

                    {{-- HWID --}}
                    <div>
                        <p class="text-xs text-gray-500 mb-1">HWID</p>
                        <p class="text-xs font-mono text-gray-700">
                            {{ $license->hwid ? substr($license->hwid, 0, 16) . '...' : 'Belum terikat' }}
                        </p>
                    </div>

                    {{-- Expired --}}
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Masa Aktif</p>
                        <p class="text-sm font-medium {{ $isExpiringSoon ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $license->expired_at ? $license->expired_at->format('d M Y') : 'Seumur Hidup' }}
                            @if ($isExpiringSoon)
                                <span class="text-xs">({{ $license->expired_at->diffForHumans() }})</span>
                            @endif
                        </p>
                    </div>

                    {{-- Last used --}}
                    <p class="text-xs text-gray-400">
                        Terakhir digunakan: {{ $license->last_used_at?->diffForHumans() ?? '—' }}
                    </p>
                </div>

                {{-- Footer Actions --}}
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2">
                    @if ($license->isActive())
                        {{-- Reset HWID --}}
                        @if ($license->canResetHwid())
                            <button
                                x-data="{ open: false }"
                                @click="open = true"
                                class="text-xs px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg font-medium hover:bg-orange-200 transition-colors">
                                Reset HWID (sisa {{ $license->product->max_hwid_resets - $license->hwid_reset_count }}x)
                            </button>

                            {{-- Modal konfirmasi reset HWID --}}
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                                <div @click.outside="open = false" class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6">
                                    <h3 class="font-bold text-gray-900 mb-2">Konfirmasi Reset HWID</h3>
                                    <p class="text-sm text-gray-600 mb-1">Setelah reset, perangkat lama tidak bisa digunakan sampai HWID baru terikat.</p>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Sisa reset: <strong>{{ $license->product->max_hwid_resets - $license->hwid_reset_count }}x</strong>
                                    </p>
                                    <div class="flex gap-3">
                                        <form method="POST" action="{{ route('user.licenses.reset-hwid', $license) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                                                Ya, Reset
                                            </button>
                                        </form>
                                        <button @click="open = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                            Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @elseif ($license->hwid_last_reset_at)
                            <span class="text-xs text-gray-400 py-1.5">
                                Reset berikutnya: {{ $license->nextHwidResetAllowedAt()?->format('d M Y') }}
                            </span>
                        @endif

                        {{-- Download Script --}}
                        @if ($license->product?->hasScript())
                            <a href="{{ route('user.licenses.download', $license) }}"
                                class="text-xs px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg font-medium hover:bg-blue-200 transition-colors">
                                Download Script
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center">
                <p class="text-gray-400 mb-2">Tidak ada lisensi ditemukan.</p>
                <p class="text-sm text-gray-400">Hubungi admin untuk mendapatkan lisensi.</p>
            </div>
        @endforelse
    </div>

</x-dashboard.user.layouts.app>
