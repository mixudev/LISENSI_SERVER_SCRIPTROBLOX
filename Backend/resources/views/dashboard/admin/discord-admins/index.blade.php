@extends('dashboard.admin.layouts.main')
@section('title', 'Manajemen Admin Discord')
@section('content')

{{-- PAGE HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Admin Discord</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $admins->total() }} admin terdaftar</p>
    </div>
    <button onclick="AppModal.open('modalAddAdmin')"
        class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors flex items-center gap-1.5 shadow-lg shadow-violet-500/20">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Admin
    </button>
</div>

{{-- FILTER & SEARCH --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="flex-1 min-w-[220px]">
        <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Cari Admin (Discord ID / Username / Note)</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="ID, username, atau catatan..."
                class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
        </div>
    </div>
    <div class="flex gap-2 items-end">
        <button type="submit"
            class="px-4 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">
            Cari
        </button>
        <a href="{{ route('admin.discord-admins.index') }}"
            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- TABLE --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
            @if($admins->total() > 0)
                Menampilkan
                <strong class="text-slate-700 dark:text-slate-200">{{ $admins->firstItem() }}–{{ $admins->lastItem() }}</strong>
                dari
                <strong class="text-slate-700 dark:text-slate-200">{{ number_format($admins->total()) }}</strong>
                admin Discord
            @else
                0 admin Discord ditemukan
            @endif
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Pengguna Discord</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Discord ID</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Catatan</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Terdaftar</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($admins as $admin)
                @php
                    $statusBadge = $admin->is_active
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40'
                        : 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-700/40';
                    $avatarUrl = $admin->avatar_url ?: 'https://cdn.discordapp.com/embed/avatars/0.png';
                @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                    {{-- Avatar + Username --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full overflow-hidden shrink-0 bg-slate-200 dark:bg-slate-700 border border-slate-200 dark:border-slate-700">
                                <img src="{{ $avatarUrl }}" alt="{{ $admin->username ?? 'User' }}"
                                    class="w-full h-full object-cover"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                <div class="w-full h-full hidden items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 127.14 96.36">
                                        <path d="M107.7,8.07A105.15,105.15,0,0,0,77.26,0a77.19,77.19,0,0,0-3.3,6.83A96.67,96.67,0,0,0,53.22,6.83,77.19,77.19,0,0,0,49.88,0,105.15,105.15,0,0,0,19.44,8.07C3.66,31.58-1.86,54.65,1,77.53A105.73,105.73,0,0,0,32,96.36a77.7,77.7,0,0,0,6.63-10.85,68.43,68.43,0,0,1-10.5-5c.88-.65,1.72-1.34,2.53-2a75.58,75.58,0,0,0,73,0c.81.71,1.65,1.4,2.53,2a68.43,68.43,0,0,1-10.5,5,77.7,77.7,0,0,0,6.63,10.85,105.73,105.73,0,0,0,31-18.83C129.87,50.77,123.36,28,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53S36.18,40.36,42.45,40.36,53.83,46,53.83,53,48.72,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.24,60,73.24,53S78.41,40.36,84.69,40.36,96.07,46,96.07,53,91,65.69,84.69,65.69Z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $admin->username ?? '—' }}
                                </p>
                                <a href="https://discord.com/users/{{ $admin->discord_id }}" target="_blank"
                                    class="text-[10px] text-violet-500 hover:text-violet-600 hover:underline mt-0.5 block">
                                    Buka Profil ↗
                                </a>
                            </div>
                        </div>
                    </td>
                    {{-- Discord ID --}}
                    <td class="px-5 py-3">
                        <span class="text-xs font-mono font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{{ $admin->discord_id }}</span>
                    </td>
                    {{-- Note --}}
                    <td class="px-5 py-3">
                        <span class="text-xs text-slate-500 dark:text-slate-400 max-w-[200px] block truncate" title="{{ $admin->note }}">{{ $admin->note ?? '—' }}</span>
                    </td>
                    {{-- Status --}}
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $statusBadge }}">
                            {{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    {{-- Terdaftar --}}
                    <td class="px-5 py-3">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $admin->created_at->format('d M Y, H:i') }}</span>
                    </td>
                    {{-- Aksi --}}
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Edit --}}
                            <button onclick="openEditAdmin({{ $admin->id }}, '{{ $admin->discord_id }}', '{{ addslashes($admin->username ?? '') }}', '{{ addslashes($admin->note ?? '') }}', {{ $admin->is_active ? 'true' : 'false' }}, '{{ $admin->avatar_url }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>

                            {{-- Toggle Active --}}
                            <button onclick="document.getElementById('formToggleAdmin-{{ $admin->id }}').submit()"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold transition-colors border
                                    {{ $admin->is_active
                                        ? 'text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-700/40 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                                        : 'text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-700/40 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' }}">
                                @if ($admin->is_active)
                                    Nonaktifkan
                                @else
                                    Aktifkan
                                @endif
                            </button>
                            <form id="formToggleAdmin-{{ $admin->id }}" action="{{ route('admin.discord-admins.toggle', $admin) }}" method="POST" class="hidden">
                                @csrf @method('PATCH')
                            </form>

                            {{-- Delete --}}
                            <button onclick="confirmDeleteAdmin({{ $admin->id }}, '{{ $admin->discord_id }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700/40 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                Hapus
                            </button>
                            <form id="formDeleteAdmin-{{ $admin->id }}" action="{{ route('admin.discord-admins.destroy', $admin) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-400">Tidak ada admin Discord ditemukan</p>
                            <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Gunakan tombol "Tambah Admin" di kanan atas untuk mendaftarkan baru.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($admins->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <p class="text-xs text-slate-400 font-mono">Hal. {{ $admins->currentPage() }} / {{ $admins->lastPage() }}</p>
        <div class="text-xs">{{ $admins->withQueryString()->links() }}</div>
    </div>
    @endif
</div>

{{-- MODAL TAMBAH ADMIN --}}
@push('modals')
<x-allert.app-modal
    id="modalAddAdmin"
    maxWidth="md"
    title="Tambah Admin Discord"
    description="Masukkan Discord User ID — username dan foto profil akan otomatis terisi."
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>'>

    <form id="formAddAdmin" action="{{ route('admin.discord-admins.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            {{-- Discord ID + Preview --}}
            <div>
                <label for="input_discord_id" class="block text-xs font-semibold text-slate-400 mb-1">DISCORD USER ID</label>
                <div class="flex gap-2">
                    <input type="text" id="input_discord_id" name="discord_id" placeholder="Contoh: 1140617759257014402" required
                        class="flex-1 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                    <button type="button" onclick="lookupDiscordUser()"
                        id="btnLookup"
                        class="px-3 py-2 text-xs font-bold text-violet-600 dark:text-violet-400 border border-violet-300 dark:border-violet-700 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors whitespace-nowrap">
                        Cek User
                    </button>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Copy User ID dari profil Discord (aktifkan Developer Mode di Discord).</span>
            </div>

            {{-- Preview User --}}
            <div id="discordPreview" class="hidden items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded">
                <img id="previewAvatar" src="" alt="" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-600">
                <div>
                    <p id="previewName" class="text-sm font-semibold text-slate-800 dark:text-slate-100"></p>
                    <p id="previewUsername" class="text-xs text-slate-400 font-mono"></p>
                </div>
                <svg class="w-5 h-5 text-emerald-500 ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            {{-- Hidden fields untuk avatar & username --}}
            <input type="hidden" id="input_username" name="username">
            <input type="hidden" id="input_avatar_url" name="avatar_url">

            <div>
                <label for="input_note" class="block text-xs font-semibold text-slate-400 mb-1">CATATAN / KETERANGAN</label>
                <input type="text" id="input_note" name="note" placeholder="Contoh: Lead Programmer / Co-Owner"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="input_is_active" name="is_active" value="1" checked
                    class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                <label for="input_is_active" class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase select-none cursor-pointer">Aktifkan Langsung</label>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button onclick="document.getElementById('formAddAdmin').submit()" class="modal-btn-primary">
            Simpan Admin
        </button>
        <button onclick="AppModal.close('modalAddAdmin')" class="modal-btn-cancel">Batal</button>
    </x-slot>
</x-allert.app-modal>

{{-- MODAL EDIT ADMIN --}}
<x-allert.app-modal
    id="modalEditAdmin"
    maxWidth="md"
    title="Edit Admin Discord"
    description="Ubah catatan atau status admin Discord."
    iconColor="slate"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>

    <form id="formEditAdmin" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            {{-- Preview User --}}
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded">
                <img id="editPreviewAvatar" src="" alt="" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-600 shrink-0">
                <div>
                    <p id="editPreviewName" class="text-sm font-semibold text-slate-800 dark:text-slate-100"></p>
                    <p id="editPreviewId" class="text-xs text-slate-400 font-mono"></p>
                </div>
            </div>

            <input type="hidden" id="edit_username" name="username">
            <input type="hidden" id="edit_avatar_url" name="avatar_url">

            <div>
                <label for="edit_note" class="block text-xs font-semibold text-slate-400 mb-1">CATATAN / KETERANGAN</label>
                <input type="text" id="edit_note" name="note" placeholder="Contoh: Lead Programmer"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="edit_admin_is_active" name="is_active" value="1"
                    class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                <label for="edit_admin_is_active" class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase select-none cursor-pointer">Status Aktif</label>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button onclick="document.getElementById('formEditAdmin').submit()" class="modal-btn-primary">
            Simpan Perubahan
        </button>
        <button onclick="AppModal.close('modalEditAdmin')" class="modal-btn-cancel">Batal</button>
    </x-slot>
</x-allert.app-modal>
@endpush

@push('scripts')
<script>
const ADMIN_ROUTES = @json($admins->mapWithKeys(fn($a) => [$a->id => route('admin.discord-admins.update', $a)]));
const LOOKUP_URL  = "{{ route('admin.discord-admins.lookup') }}";

// ─── Lookup Discord User saat tambah admin ────────────────────────────────
async function lookupDiscordUser() {
    const discordId = document.getElementById('input_discord_id').value.trim();
    if (!discordId || !/^\d{16,20}$/.test(discordId)) {
        AppPopup.alert({ title: 'ID tidak valid', description: 'Discord User ID harus berupa angka 16–20 digit.', iconColor: 'amber' });
        return;
    }

    const btn = document.getElementById('btnLookup');
    btn.textContent = 'Mencari...';
    btn.disabled = true;

    try {
        const res = await fetch(`${LOOKUP_URL}?discord_id=${discordId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();

        if (json.status && json.data) {
            const { username, global_name, avatar_url } = json.data;

            // Isi hidden fields
            document.getElementById('input_username').value  = username ?? '';
            document.getElementById('input_avatar_url').value = avatar_url ?? '';

            // Tampilkan preview
            document.getElementById('previewAvatar').src     = avatar_url;
            document.getElementById('previewName').textContent  = global_name ?? username ?? 'Unknown';
            document.getElementById('previewUsername').textContent = '@' + (username ?? discordId);
            document.getElementById('discordPreview').classList.remove('hidden');
            document.getElementById('discordPreview').classList.add('flex');
        } else {
            AppPopup.alert({ title: 'User tidak ditemukan', description: json.message ?? 'Pastikan Discord ID benar.', iconColor: 'red' });
        }
    } catch (e) {
        AppPopup.alert({ title: 'Gagal menghubungi server', description: 'Coba lagi beberapa saat.', iconColor: 'red' });
    } finally {
        btn.textContent = 'Cek User';
        btn.disabled = false;
    }
}

// Lookup saat Enter di field ID
document.getElementById('input_discord_id')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); lookupDiscordUser(); }
});

// Reset preview saat modal ditutup
document.getElementById('input_discord_id')?.addEventListener('input', () => {
    document.getElementById('discordPreview').classList.add('hidden');
    document.getElementById('discordPreview').classList.remove('flex');
    document.getElementById('input_username').value  = '';
    document.getElementById('input_avatar_url').value = '';
});

// ─── Buka modal Edit Admin ────────────────────────────────────────────────
function openEditAdmin(id, discordId, username, note, isActive, avatarUrl) {
    const form = document.getElementById('formEditAdmin');
    form.action = ADMIN_ROUTES[id];

    document.getElementById('edit_username').value       = username;
    document.getElementById('edit_avatar_url').value     = avatarUrl;
    document.getElementById('edit_note').value           = note;
    document.getElementById('edit_admin_is_active').checked = isActive;

    // Preview
    document.getElementById('editPreviewAvatar').src         = avatarUrl || 'https://cdn.discordapp.com/embed/avatars/0.png';
    document.getElementById('editPreviewName').textContent   = username || 'Unknown User';
    document.getElementById('editPreviewId').textContent     = discordId;

    AppModal.open('modalEditAdmin');
}

// ─── Konfirmasi hapus admin ───────────────────────────────────────────────
function confirmDeleteAdmin(id, discordId) {
    AppPopup.confirm({
        title: 'Hapus Admin Discord?',
        description: `Apakah Anda yakin ingin menghapus admin dengan Discord ID <strong>${discordId}</strong>? Tindakan ini akan segera membatalkan hak akses admin mereka di bot.`,
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        onConfirm: () => {
            document.getElementById(`formDeleteAdmin-${id}`).submit();
        }
    });
}
</script>
@endpush

@endsection
