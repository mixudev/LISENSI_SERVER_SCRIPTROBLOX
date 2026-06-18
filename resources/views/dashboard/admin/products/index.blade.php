@extends('dashboard.admin.layouts.main')
@section('title', 'Produk & Script')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Produk & Script</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $products->count() }} produk · Kelola script yang di-serve ke executor</p>
    </div>
    <button onclick="AppModal.open('modalAddProduct')"
        class="inline-flex items-center gap-2 px-3 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Produk
    </button>
</div>

{{-- GitHub PAT status --}}
@php
    $patOk = $githubPatInfo['configured'] ?? false;
    $patType = $githubPatInfo['type'] ?? 'missing';
@endphp
<div class="mb-5 rounded border {{ $patOk ? 'border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/40 dark:bg-emerald-900/10' : 'border-amber-200 dark:border-amber-800/50 bg-amber-50/40 dark:bg-amber-900/10' }} p-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">GitHub Private Repo</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                @if ($patOk)
                    PAT aktif — {{ $githubPatInfo['label'] ?? 'Token' }} · {{ $githubPatInfo['recommendation'] ?? '' }}
                @else
                    <code>GITHUB_PAT</code> belum diset. Gunakan <strong>Classic PAT</strong> (<code>ghp_</code>) dengan scope <code>repo</code>, atau Fine-grained dengan Contents: Read.
                @endif
            </p>
        </div>
        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $patOk ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400' }}">
            {{ $patOk ? 'PAT OK' : 'PAT MISSING' }}
        </span>
    </div>
</div>

{{-- Folder scanner --}}
<div class="mb-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Folder Script Lokal</h3>
        <span class="text-[10px] font-mono text-slate-400">storage/app/private/scripts/</span>
    </div>
    <div class="p-4 flex flex-wrap gap-3">
        @forelse ($localFolders as $folder => $info)
        <div class="flex items-center gap-2 px-3 py-2 border {{ $info['has_loader'] ? 'border-emerald-200 dark:border-emerald-700/40 bg-emerald-50/50 dark:bg-emerald-900/10' : 'border-amber-200 dark:border-amber-700/40 bg-amber-50/50 dark:bg-amber-900/10' }}">
            <svg class="w-3.5 h-3.5 {{ $info['has_loader'] ? 'text-emerald-500' : 'text-amber-500' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                @if ($info['has_loader'])
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                @endif
            </svg>
            <div>
                <p class="text-[11px] font-bold font-mono text-slate-700 dark:text-slate-300">{{ $folder }}</p>
                <p class="text-[9px] text-slate-400">{{ $info['file_count'] }} file · {{ $info['size_kb'] }} KB
                    {{ $info['has_loader'] ? '· ✓ loader.lua' : '· ⚠ no loader.lua' }}</p>
            </div>
        </div>
        @empty
        <p class="text-xs text-slate-400 italic">Tidak ada folder ditemukan di storage/app/private/scripts/</p>
        @endforelse
    </div>
</div>

{{-- Products table --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Produk</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Script Source</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Lisensi</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Config</th>
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($products as $product)
                @php
                    $sv = $product->status instanceof \App\Enums\ProductStatus ? $product->status->value : $product->status;
                    $sBadge = match($sv) {
                        'active'      => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-700/40',
                        'inactive'    => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                        'maintenance' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-700/40',
                        default       => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                    $isGithub  = $product->script_source === 'github';
                    $hasScript = $isGithub ? (bool)$product->github_repo : $product->hasLocalScript();
                @endphp
                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $product->name }}</p>
                        <p class="text-[10px] font-mono text-slate-400">v{{ $product->version }} · {{ $product->slug }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($isGithub)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                            </svg>
                            <div>
                                <p class="text-[11px] font-mono text-slate-700 dark:text-slate-300">{{ $product->github_repo }}</p>
                                <p class="text-[9px] text-slate-400">{{ $product->github_branch ?? 'main' }} · {{ $product->github_path }}</p>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 {{ $hasScript ? 'text-emerald-500' : 'text-amber-500' }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <div>
                                <p class="text-[11px] font-mono text-slate-700 dark:text-slate-300">{{ $product->script_folder ?? '— tidak diset' }}</p>
                                <p class="text-[9px] text-slate-400">{{ $hasScript ? '✓ loader.lua ada' : '⚠ loader.lua tidak ditemukan' }}</p>
                            </div>
                        </div>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $product->licenses_count }}</p>
                        <p class="text-[10px] text-slate-400">{{ $product->active_licenses_count }} aktif</p>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">
                            <span class="font-semibold {{ $product->access_level === 'admin' ? 'text-violet-600 dark:text-violet-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $product->access_level === 'admin' ? 'Admin only' : 'User' }}
                            </span>
                            · {{ $product->place_ids ? count($product->place_ids).' place' : 'Universal' }}
                        </p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $sBadge }}">{{ $sv }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if ($isGithub)
                            <button onclick="refreshScript({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-700/40 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Refresh cache GitHub">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Refresh
                            </button>
                            @endif
                            <button onclick="AppModal.open('modalEditProduct-{{ $product->id }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-700/40 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <button onclick="confirmDeleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700/40 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="flex flex-col items-center justify-center py-14 text-center">
                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-sm font-semibold text-slate-400">Belum ada produk</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('modals')
{{-- MODAL: Tambah Produk --}}
<x-allert.app-modal id="modalAddProduct" maxWidth="lg" title="Tambah Produk" description="Produk mengarah ke satu folder script"
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'>
    <form id="formAddProduct" method="POST" action="{{ route('admin.products.store') }}">
        @csrf
        @include('dashboard.admin.products._form', ['product' => null, 'localFolders' => $localFolders])
    </form>
    <x-slot name="footer">
        <button onclick="AppModal.close('modalAddProduct')" class="modal-btn-cancel">Batal</button>
        <button onclick="document.getElementById('formAddProduct').submit()" class="modal-btn-primary">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Simpan
        </button>
    </x-slot>
</x-allert.app-modal>
@endpush

@foreach ($products as $product)
@push('modals')
<x-allert.app-modal id="modalEditProduct-{{ $product->id }}" maxWidth="lg"
    title="Edit: {{ $product->name }}" description="v{{ $product->version }}"
    iconColor="indigo"
    icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'>
    <form id="formEditProduct-{{ $product->id }}" method="POST" action="{{ route('admin.products.update', $product) }}">
        @csrf @method('PUT')
        @include('dashboard.admin.products._form', ['product' => $product, 'localFolders' => $localFolders])
    </form>
    <x-slot name="footer">
        <button onclick="AppModal.close('modalEditProduct-{{ $product->id }}')" class="modal-btn-cancel">Batal</button>
        <button onclick="document.getElementById('formEditProduct-{{ $product->id }}').submit()" class="modal-btn-primary">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Simpan
        </button>
    </x-slot>
</x-allert.app-modal>
@endpush
@endforeach

{{-- Hidden delete forms --}}
@foreach ($products as $product)
<form id="formDeleteProduct-{{ $product->id }}" method="POST" action="{{ route('admin.products.destroy', $product) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endforeach

@push('scripts')
<script>
var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

window.ProductForm = {
    activeClass: ['border-violet-400', 'bg-violet-50', 'dark:bg-violet-900/20', 'text-violet-700', 'dark:text-violet-400'],
    inactiveClass: ['border-slate-200', 'dark:border-slate-700', 'text-slate-500'],

    _setChipState(selector, activeValue, attr) {
        document.querySelectorAll(selector).forEach(function (label) {
            var input = label.querySelector('input[type="radio"]');
            if (!input) return;
            var active = input.value === activeValue;
            label.classList.remove(...ProductForm.activeClass, ...ProductForm.inactiveClass);
            label.classList.add(...(active ? ProductForm.activeClass : ProductForm.inactiveClass));
            if (active) input.checked = true;
        });
    },

    toggleSource(formId, source) {
        var localSection  = document.getElementById(formId + '-local');
        var githubSection = document.getElementById(formId + '-github');
        if (!localSection || !githubSection) return;

        var isLocal = source === 'local';
        localSection.classList.toggle('hidden', !isLocal);
        githubSection.classList.toggle('hidden', isLocal);

        localSection.querySelectorAll('select, input').forEach(function (el) {
            el.disabled = !isLocal;
        });
        githubSection.querySelectorAll('input, select, button').forEach(function (el) {
            el.disabled = isLocal;
        });

        ProductForm._setChipState('.source-toggle-' + formId, source);
    },

    toggleAccess(formId, level) {
        ProductForm._setChipState('.access-toggle-' + formId, level);
    },

    initForm(formId, source) {
        ProductForm.toggleSource(formId, source || 'local');
        var checkedAccess = document.querySelector('.access-toggle-' + formId + ' input[type="radio"]:checked');
        if (checkedAccess) {
            ProductForm.toggleAccess(formId, checkedAccess.value);
        }
    },

    scanGithub(formId) {
        var root = document.querySelector('[data-product-form="' + formId + '"]');
        if (!root) return;

        var repoInput = root.querySelector('[data-github-repo]');
        var branchSelect = root.querySelector('[data-github-branch]');
        var pathSelect = root.querySelector('[data-github-path]');
        var statusBox = document.getElementById(formId + '-github-status');
        var scanBtn = root.querySelector('[data-github-scan-btn]');

        if (!repoInput || !repoInput.value.trim()) {
            AppPopup.warning({ title: 'Repo kosong', description: 'Isi owner/repo atau URL GitHub terlebih dahulu.' });
            return;
        }

        scanBtn.disabled = true;
        scanBtn.textContent = 'Scanning...';
        statusBox.classList.remove('hidden');
        statusBox.className = 'rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-3 text-[11px]';
        statusBox.textContent = 'Menghubungi GitHub API...';

        fetch('{{ route('admin.products.github.inspect', absolute: false) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                github_repo: repoInput.value.trim(),
                github_branch: branchSelect ? branchSelect.value : null,
            }),
        })
        .then(function (r) {
            return r.text().then(function (text) {
                var data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (e) {
                    throw new Error('Respons server tidak valid (HTTP ' + r.status + ').');
                }
                return { status: r.status, data: data };
            });
        })
        .then(function (res) {
            var d = res.data;
            if (!d.ok) {
                statusBox.className = 'rounded border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/10 p-3 text-[11px] text-red-700 dark:text-red-400';
                statusBox.textContent = d.message || (d.errors && Object.values(d.errors).flat().join(' ')) || 'Gagal scan repo.';
                return;
            }

            repoInput.value = d.repo;
            if (branchSelect && d.branches) {
                branchSelect.innerHTML = '';
                d.branches.forEach(function (b) {
                    var opt = document.createElement('option');
                    opt.value = b;
                    opt.textContent = b + (b === d.default_branch ? ' (default)' : '');
                    if (b === (d.default_branch || 'main')) opt.selected = true;
                    branchSelect.appendChild(opt);
                });
            }
            if (pathSelect && d.loaders) {
                pathSelect.innerHTML = '';
                d.loaders.forEach(function (loader) {
                    var opt = document.createElement('option');
                    opt.value = loader.path;
                    opt.textContent = loader.path + (loader.path === d.recommended_path ? ' ★ recommended' : '');
                    if (loader.path === d.recommended_path) opt.selected = true;
                    pathSelect.appendChild(opt);
                });
            }

            var loaderCount = (d.loaders || []).length;
            statusBox.className = 'rounded border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/10 p-3 text-[11px] text-emerald-800 dark:text-emerald-300';
            statusBox.innerHTML = '<strong>✓ Repo terhubung</strong> · ' + d.repo + ' · ' + loaderCount + ' loader.lua ditemukan'
                + (d.module_prefix ? ' · modul base: <code class="font-mono">' + d.module_prefix + '/</code>' : '');
        })
        .catch(function (err) {
            statusBox.className = 'rounded border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/10 p-3 text-[11px] text-red-700 dark:text-red-400';
            statusBox.textContent = (err && err.message) ? err.message : 'Gagal menghubungi server. Pastikan Anda login dan buka halaman dari URL yang sama dengan APP_URL.';
        })
        .finally(function () {
            scanBtn.disabled = false;
            scanBtn.textContent = 'Scan Repo';
        });
    }
};

function confirmDeleteProduct(id, name) {
    AppPopup.confirm({
        title: 'Hapus Produk?',
        description: 'Produk <strong>' + name + '</strong> akan dihapus. Tidak bisa jika masih ada lisensi aktif.',
        confirmText: 'Ya, Hapus', cancelText: 'Batal',
        onConfirm: () => document.getElementById('formDeleteProduct-' + id).submit()
    });
}

function refreshScript(productId, productName) {
    AppPopup.warning({
        title: 'Refresh Cache Script?',
        description: 'Cache GitHub script <strong>' + productName + '</strong> akan dihapus. Request inject berikutnya akan fetch ulang dari GitHub.',
        confirmText: 'Ya, Refresh', cancelText: 'Batal',
        onConfirm: () => {
            fetch('/admin/products/' + productId + '/refresh-script', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => AppPopup.success({ title: d.ok ? 'Berhasil' : 'Gagal', description: d.message }))
            .catch(() => AppPopup.error({ title: 'Error', description: 'Gagal menghubungi server.' }));
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    ProductForm.initForm('form-add', 'local');
    @foreach ($products as $product)
    ProductForm.initForm('form-edit-{{ $product->id }}', @json($product->script_source ?? 'local'));
    @endforeach
});
</script>
@endpush

@endsection
