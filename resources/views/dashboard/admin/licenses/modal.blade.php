
{{-- MODAL: Generate 1 Lisensi --}}
<x-allert.app-modal id="modalGenerate" maxWidth="md" title="Generate Lisensi" description="Buat satu license key baru"
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>'>
    <form id="formGenerate" method="POST" action="{{ route('admin.licenses.store') }}">
        @csrf
        <div class="flex flex-col gap-4">
            {{-- Tipe Lisensi --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">
                    Tipe Lisensi <span class="text-red-500 normal-case font-normal">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-violet-400 dark:hover:border-violet-600 transition-colors has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:bg-violet-900/20">
                        <input type="radio" name="license_type" value="user" checked class="accent-violet-600">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">User</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Akses semua script</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-violet-400 dark:hover:border-violet-600 transition-colors has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:bg-violet-900/20">
                        <input type="radio" name="license_type" value="admin" class="accent-violet-600">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Admin</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Script admin khusus</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Durasi --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">
                    Durasi (hari)
                </label>
                <input type="number" name="duration_days" min="0" placeholder="Kosongkan = Lifetime"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                <p class="text-[10px] text-slate-400 mt-1">0 atau kosong = tidak ada expired (Lifetime)</p>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Catatan Internal</label>
                <textarea name="notes" rows="2" placeholder="Opsional..."
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 resize-none"></textarea>
            </div>
        </div>
    </form>
    <x-slot name="footer">
        <button onclick="AppModal.close('modalGenerate')" class="modal-btn-cancel">Batal</button>
        <button onclick="document.getElementById('formGenerate').submit()" class="modal-btn-primary">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Generate
        </button>
    </x-slot>
</x-allert.app-modal>

{{-- MODAL: Generate Bulk --}}
<x-allert.app-modal id="modalGenerateBulk" maxWidth="md" title="Generate Bulk Lisensi" description="Buat banyak license key sekaligus (maks 100)"
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>'>
    <form id="formGenerateBulk" method="POST" action="{{ route('admin.licenses.bulk') }}">
        @csrf
        <div class="flex flex-col gap-4">
            {{-- Tipe Lisensi --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">
                    Tipe Lisensi <span class="text-red-500 normal-case font-normal">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-violet-400 dark:hover:border-violet-600 transition-colors has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:bg-violet-900/20">
                        <input type="radio" name="license_type" value="user" checked class="accent-violet-600">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">User</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Akses semua script</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-violet-400 dark:hover:border-violet-600 transition-colors has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:bg-violet-900/20">
                        <input type="radio" name="license_type" value="admin" class="accent-violet-600">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Admin</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Script admin khusus</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Jumlah --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">
                    Jumlah Key <span class="text-red-500 normal-case font-normal">*</span>
                </label>
                <input type="number" name="count" value="5" min="1" max="100" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
            </div>

            {{-- Durasi --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">
                    Durasi (hari)
                </label>
                <input type="number" name="duration_days" min="0" placeholder="Kosongkan = Lifetime"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                <p class="text-[10px] text-slate-400 mt-1">0 atau kosong = tidak ada expired (Lifetime)</p>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Catatan Internal</label>
                <textarea name="notes" rows="2" placeholder="Opsional..."
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 resize-none"></textarea>
            </div>
        </div>
    </form>
    <x-slot name="footer">
        <button onclick="AppModal.close('modalGenerateBulk')" class="modal-btn-cancel">Batal</button>
        <button onclick="document.getElementById('formGenerateBulk').submit()" class="modal-btn-primary">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Generate Bulk
        </button>
    </x-slot>
</x-allert.app-modal>

@push('scripts')
@endpush
