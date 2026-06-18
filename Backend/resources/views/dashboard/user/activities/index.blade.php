<x-dashboard.user.layouts.app title="Riwayat Aktivitas">

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Aksi</label>
            <select name="action" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ str_replace('_', ' ', ucfirst($action)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Filter</button>
    </form>

    {{-- Timeline --}}
    <div class="relative">
        @if ($activities->isEmpty())
            <div class="bg-white rounded-xl p-12 text-center text-gray-400 shadow-sm border border-gray-100">
                <p>Belum ada riwayat aktivitas.</p>
            </div>
        @else
            <div class="border-l-2 border-gray-200 ml-3 flex flex-col gap-0">
                @foreach ($activities as $activity)
                    @php
                        $iconColor = match($activity->action) {
                            'login', 'logout'           => 'bg-blue-500',
                            'reset_hwid'                => 'bg-orange-500',
                            'download_product'          => 'bg-purple-500',
                            'license_activated'         => 'bg-green-500',
                            'license_banned', 'license_suspended' => 'bg-red-500',
                            default                     => 'bg-gray-400',
                        };
                    @endphp
                    <div class="relative pl-8 pb-6">
                        {{-- Dot --}}
                        <div class="absolute -left-2 top-0 w-4 h-4 rounded-full border-2 border-white {{ $iconColor }}"></div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ str_replace('_', ' ', ucfirst($activity->action)) }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $activity->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            @if ($activity->license)
                                <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $activity->license->license_key }}</p>
                            @endif
                            @if ($activity->ip)
                                <p class="text-xs text-gray-400 mt-1">IP: {{ $activity->ip }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($activities->hasPages())
                <div class="mt-4">
                    {{ $activities->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>

</x-dashboard.user.layouts.app>
