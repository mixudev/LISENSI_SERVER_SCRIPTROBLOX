@extends('dashboard.admin.layouts.main')
@section('title', 'Konfigurasi API Key AI')
@section('content')

{{-- PAGE HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">API Key AI Bot</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $keys->count() }} API Key terdaftar</p>
    </div>
    <button onclick="AppModal.open('modalAddAiKey')"
        class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors flex items-center gap-1.5 shadow-lg shadow-violet-500/20">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah API Key
    </button>
</div>

{{-- CARDS STATS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-violet-50 dark:bg-violet-950/40 text-violet-600 dark:text-violet-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Fallback AI Aktif</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">{{ $keys->where('is_active', true)->count() }} Key</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Total Pemanggilan</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">{{ number_format($keys->sum('usage_count')) }} kali</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest leading-none">Indikasi Error / Limit</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-200 mt-1.5">{{ $keys->sum('error_count') }} Deteksi</p>
            </div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Daftar API Key Terdaftar (Diurutkan berdasarkan Prioritas)</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Prioritas / Provider</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Model</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">API Key</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Penggunaan</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Error Count</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Terakhir Digunakan</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($keys as $key)
                @php
                    $statusColor = $key->is_active
                        ? ($key->error_count >= 5 ? 'text-amber-500' : 'text-emerald-500')
                        : 'text-red-500';

                    $providerBadge = match($key->provider) {
                        'gemini'     => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-700/40',
                        'groq'       => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-700/40',
                        'openrouter' => 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-700/40',
                        default      => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                    {{-- Priority / Provider --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-mono text-xs font-bold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 shrink-0">
                                P{{ $key->priority }}
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $providerBadge }}">
                                {{ ucfirst($key->provider) }}
                            </span>
                        </div>
                    </td>
                    {{-- Model --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $key->model }}</span>
                    </td>
                    {{-- API Key (censored) --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400">
                            {{ substr($key->api_key, 0, 8) }}•••{{ substr($key->api_key, -6) }}
                        </span>
                    </td>
                    {{-- Usage --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ number_format($key->usage_count) }} kali</span>
                    </td>
                    {{-- Error Count --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-semibold {{ $key->error_count > 0 ? 'text-red-500' : 'text-slate-400' }}">
                                {{ $key->error_count }}
                            </span>
                            @if ($key->error_count > 0)
                                <button onclick="document.getElementById('formResetError-{{ $key->id }}').submit()"
                                    class="text-[9px] font-bold text-violet-600 dark:text-violet-400 hover:underline border-0 bg-transparent cursor-pointer" title="Reset hitungan error">
                                    Reset
                                </button>
                                <form id="formResetError-{{ $key->id }}" action="{{ route('admin.ai-keys.reset-errors', $key) }}" method="POST" class="hidden">
                                    @csrf @method('PATCH')
                                </form>
                            @endif
                        </div>
                    </td>
                    {{-- Last Used --}}
                    <td class="px-5 py-3.5">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Belum pernah' }}</span>
                    </td>
                    {{-- Aksi --}}
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Edit --}}
                            <button onclick="openEditAiKey({{ $key->id }}, '{{ $key->provider }}', '{{ $key->model }}', {{ $key->priority }}, {{ $key->is_active ? 'true' : 'false' }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>

                            {{-- Toggle Active --}}
                            <button onclick="document.getElementById('formToggleKey-{{ $key->id }}').submit()"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold transition-colors border
                                    {{ $key->is_active
                                        ? 'text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-700/40 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                                        : 'text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-700/40 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' }}">
                                @if ($key->is_active)
                                    Nonaktifkan
                                @else
                                    Aktifkan
                                @endif
                            </button>
                            <form id="formToggleKey-{{ $key->id }}" action="{{ route('admin.ai-keys.toggle', $key) }}" method="POST" class="hidden">
                                @csrf @method('PATCH')
                            </form>

                            {{-- Delete --}}
                            <button onclick="confirmDeleteKey({{ $key->id }}, '{{ $key->provider }}', '{{ $key->model }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700/40 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                Hapus
                            </button>
                            <form id="formDeleteKey-{{ $key->id }}" action="{{ route('admin.ai-keys.destroy', $key) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-400">Tidak ada API Key AI terdaftar</p>
                            <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Harap daftarkan minimal 1 API Key agar bot AI `/wolf` dapat merespons di Discord.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TAMBAH KEY --}}
@push('modals')
<x-allert.app-modal
    id="modalAddAiKey"
    maxWidth="md"
    title="Tambah API Key AI"
    description="Tambahkan kredensial API key untuk mengaktifkan respons kecerdasan buatan."
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>'>

    <form id="formAddAiKey" action="{{ route('admin.ai-keys.store') }}" method="POST">
        @csrf
        <div class="space-y-4 modal-body">
            <div>
                <label for="select_provider" class="block text-xs font-semibold text-slate-400 mb-1">PILIH PROVIDER</label>
                <select id="select_provider" name="provider" required onchange="handleProviderChange(this.value, 'input_model')"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                    <option value="" disabled selected>-- Pilih AI Provider --</option>
                    <option value="gemini">Google Gemini</option>
                    <option value="groq">Groq Cloud API</option>
                    <option value="openrouter">OpenRouter AI</option>
                </select>
            </div>

            <div>
                <label for="input_api_key" class="block text-xs font-semibold text-slate-400 mb-1">API KEY</label>
                <input type="password" id="input_api_key" name="api_key" placeholder="Masukkan API Key..." required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            </div>

            <div>
                <label for="input_model" class="block text-xs font-semibold text-slate-400 mb-1">AI MODEL</label>
                <input type="text" id="input_model" name="model" placeholder="Model akan terisi otomatis" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                <span class="text-[10px] text-slate-400 mt-1 block">Rekomendasi model diisi otomatis. Anda dapat mengubahnya jika perlu model lain.</span>
            </div>

            <div>
                <label for="input_priority" class="block text-xs font-semibold text-slate-400 mb-1">PRIORITAS PENYALAAN (PRIORITY)</label>
                <input type="number" id="input_priority" name="priority" value="1" min="1" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                <span class="text-[10px] text-slate-400 mt-1 block">Urutan eksekusi fallback. Semakin kecil angkanya (misal 1), semakin dahulu dicoba saat bot AI dipanggil.</span>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="input_is_active" name="is_active" value="1" checked
                    class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                <label for="input_is_active" class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase select-none cursor-pointer">Aktifkan Langsung</label>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button onclick="document.getElementById('formAddAiKey').submit()" class="modal-btn-primary">
            Simpan API Key
        </button>
        <button onclick="AppModal.close('modalAddAiKey')" class="modal-btn-cancel">Batal</button>
    </x-slot>
</x-allert.app-modal>

{{-- MODAL EDIT KEY --}}
<x-allert.app-modal
    id="modalEditAiKey"
    maxWidth="md"
    title="Edit API Key AI"
    description="Ubah konfigurasi API key yang sudah tersimpan."
    iconColor="slate"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>

    <form id="formEditAiKey" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4 modal-body">
            <div>
                <label for="edit_provider" class="block text-xs font-semibold text-slate-400 mb-1">PILIH PROVIDER</label>
                <select id="edit_provider" name="provider" required onchange="handleProviderChange(this.value, 'edit_model')"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                    <option value="gemini">Google Gemini</option>
                    <option value="groq">Groq Cloud API</option>
                    <option value="openrouter">OpenRouter AI</option>
                </select>
            </div>

            <div>
                <label for="edit_api_key" class="block text-xs font-semibold text-slate-400 mb-1">API KEY BARU (KOSONGKAN JIKA TIDAK DIUBAH)</label>
                <input type="password" id="edit_api_key" name="api_key" placeholder="Biarkan kosong jika tidak ingin mengubah..."
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                <span class="text-[10px] text-slate-400 mt-1 block">Kosongkan field ini jika tidak ingin mengubah API Key yang tersimpan.</span>
            </div>

            <div>
                <label for="edit_model" class="block text-xs font-semibold text-slate-400 mb-1">AI MODEL</label>
                <input type="text" id="edit_model" name="model" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            </div>

            <div>
                <label for="edit_priority" class="block text-xs font-semibold text-slate-400 mb-1">PRIORITAS PENYALAAN (PRIORITY)</label>
                <input type="number" id="edit_priority" name="priority" min="1" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1"
                    class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                <label for="edit_is_active" class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase select-none cursor-pointer">Status Aktif</label>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button onclick="document.getElementById('formEditAiKey').submit()" class="modal-btn-primary">
            Simpan Perubahan
        </button>
        <button onclick="AppModal.close('modalEditAiKey')" class="modal-btn-cancel">Batal</button>
    </x-slot>
</x-allert.app-modal>
@endpush

@push('scripts')
<script>
const RECOMMENDED_MODELS = {
    gemini: 'gemini-2.5-flash',
    groq: 'llama-3.3-70b-versatile',
    openrouter: 'google/gemini-2.5-flash'
};

const AI_KEY_ROUTES = @json($keys->mapWithKeys(fn($k) => [$k->id => route('admin.ai-keys.update', $k)]));

function handleProviderChange(provider, modelFieldId) {
    const modelInput = document.getElementById(modelFieldId);
    if (RECOMMENDED_MODELS[provider] && !modelInput.dataset.customized) {
        modelInput.value = RECOMMENDED_MODELS[provider];
    }
}

function openEditAiKey(id, provider, model, priority, isActive) {
    const form = document.getElementById('formEditAiKey');
    form.action = AI_KEY_ROUTES[id];

    // Set fields
    document.getElementById('edit_provider').value = provider;
    const modelInput = document.getElementById('edit_model');
    modelInput.value = model;
    modelInput.dataset.customized = '1'; // Prevent overwrite saat provider change
    document.getElementById('edit_priority').value = priority;
    document.getElementById('edit_api_key').value = '';
    document.getElementById('edit_is_active').checked = isActive;

    AppModal.open('modalEditAiKey');
}

// Reset customized flag saat modal ditutup
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('edit_model')?.addEventListener('input', function() {
        this.dataset.customized = '1';
    });
    document.getElementById('edit_provider')?.addEventListener('change', function() {
        const modelInput = document.getElementById('edit_model');
        delete modelInput.dataset.customized;
        handleProviderChange(this.value, 'edit_model');
    });
});

function confirmDeleteKey(id, provider, model) {
    AppPopup.confirm({
        title: 'Hapus API Key AI?',
        description: `Apakah Anda yakin ingin menghapus API Key untuk <strong>${provider}</strong> (${model})? Bot tidak akan dapat menggunakan key ini lagi sebagai fallback.`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        onConfirm: () => {
            document.getElementById(`formDeleteKey-${id}`).submit();
        }
    });
}
</script>
@endpush

@endsection
