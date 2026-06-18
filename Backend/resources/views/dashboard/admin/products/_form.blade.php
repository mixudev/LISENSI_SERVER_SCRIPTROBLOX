@php

    $formId       = $product ? 'form-edit-' . $product->id : 'form-add';

    $isGithub     = $product && $product->script_source === 'github';

    $curFolder    = $product?->script_folder;

    $curSource    = $product?->script_source ?? 'local';

    $curAccess    = $product?->access_level ?? 'user';

@endphp



<div class="space-y-4" data-product-form="{{ $formId }}"@if($product) data-exclude-product-id="{{ $product->id }}" data-current-slug="{{ $product->slug }}"@endif>



    {{-- Nama + Versi --}}

    <div class="grid grid-cols-2 gap-3">

        <div class="col-span-2 sm:col-span-1">

            <label class="modal-label">Nama Produk <span class="text-red-500">*</span></label>

            <input type="text" name="name" required placeholder="contoh: VIP, Premium..."

                value="{{ old('name', $product?->name) }}" class="modal-input" data-product-name>

            <p class="text-[10px] mt-1 hidden" data-slug-hint></p>

        </div>

        <div>

            <label class="modal-label">Versi <span class="text-red-500">*</span></label>

            <input type="text" name="version" required placeholder="1.0.0"

                value="{{ old('version', $product?->version ?? '1.0.0') }}" class="modal-input">

        </div>

    </div>



    {{-- Script Source Toggle --}}

    <div>

        <label class="modal-label">Sumber Script <span class="text-red-500">*</span></label>

        <div class="flex gap-2 mt-1.5" role="radiogroup" aria-label="Sumber script">

            @foreach (['local' => 'Lokal (Storage)', 'github' => 'GitHub Private Repo'] as $val => $lbl)

            <label data-source-option="{{ $val }}"

                class="source-toggle-{{ $formId }} flex items-center gap-2 px-3 py-2 border cursor-pointer transition-colors

                {{ $curSource === $val ? 'border-violet-400 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-400' : 'border-slate-200 dark:border-slate-700 text-slate-500 hover:border-violet-300' }}">

                <input type="radio" name="script_source" value="{{ $val }}"

                    {{ $curSource === $val ? 'checked' : '' }}

                    onchange="ProductForm.toggleSource('{{ $formId }}', '{{ $val }}')"

                    class="sr-only">

                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                    @if ($val === 'local')

                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>

                    @else

                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>

                    @endif

                </svg>

                <span class="text-xs font-semibold">{{ $lbl }}</span>

            </label>

            @endforeach

        </div>

    </div>



    {{-- LOCAL section --}}

    <div id="{{ $formId }}-local" class="{{ $isGithub ? 'hidden' : '' }}">

        <label class="modal-label">Folder Script Lokal</label>

        <select name="script_folder" class="modal-input modal-select">

            <option value="">— Pilih folder —</option>

            @foreach ($localFolders as $folder => $info)

            <option value="{{ $folder }}" {{ $curFolder === $folder ? 'selected' : '' }}>

                {{ $folder }}{{ $info['has_loader'] ? '' : ' ⚠ (no loader.lua)' }}

                · {{ $info['file_count'] }} file, {{ $info['size_kb'] }} KB

            </option>

            @endforeach

        </select>

        <p class="text-[10px] text-slate-400 mt-1">Folder di <code class="bg-slate-100 dark:bg-slate-800 px-1">storage/app/private/scripts/</code> — harus punya <code>loader.lua</code></p>

    </div>



    {{-- GITHUB section --}}

    <div id="{{ $formId }}-github" class="{{ $isGithub ? '' : 'hidden' }} space-y-3">

        {{-- PAT guide --}}

        <div class="rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-3 space-y-2">

            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Token GitHub (GITHUB_PAT di .env)</p>

            <div class="grid sm:grid-cols-2 gap-2 text-[10px] text-slate-500 dark:text-slate-400">

                <div class="border border-emerald-200 dark:border-emerald-800/50 rounded p-2 bg-emerald-50/50 dark:bg-emerald-900/10">

                    <p class="font-bold text-emerald-700 dark:text-emerald-400 mb-0.5">Classic PAT — Direkomendasikan</p>

                    <p>Prefix <code class="font-mono">ghp_</code> · scope <strong>repo</strong> (full private repo access)</p>

                </div>

                <div class="border border-blue-200 dark:border-blue-800/50 rounded p-2 bg-blue-50/50 dark:bg-blue-900/10">

                    <p class="font-bold text-blue-700 dark:text-blue-400 mb-0.5">Fine-grained PAT</p>

                    <p>Prefix <code class="font-mono">github_pat_</code> · permission <strong>Contents: Read</strong> per repo</p>

                </div>

            </div>

        </div>



        <div>

            <label class="modal-label">Repo GitHub <span class="text-red-500">*</span></label>

            <div class="flex gap-2">

                <input type="text" name="github_repo" placeholder="owner/repo atau https://github.com/owner/repo"

                    value="{{ old('github_repo', $product?->github_repo) }}"

                    class="modal-input font-mono flex-1" data-github-repo>

                <button type="button" onclick="ProductForm.scanGithub('{{ $formId }}')"

                    class="shrink-0 px-3 py-2 text-[10px] font-bold text-white bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors"

                    data-github-scan-btn>

                    Scan Repo

                </button>

            </div>

        </div>



        <div id="{{ $formId }}-github-status" class="hidden rounded border p-3 text-[11px]" data-github-status></div>



        <div class="grid grid-cols-2 gap-3">

            <div>

                <label class="modal-label">Branch</label>

                <select name="github_branch" class="modal-input modal-select font-mono" data-github-branch>

                    <option value="{{ old('github_branch', $product?->github_branch ?? 'main') }}">

                        {{ old('github_branch', $product?->github_branch ?? 'main') }}

                    </option>

                </select>

            </div>

            <div>

                <label class="modal-label">Path ke loader.lua <span class="text-red-500">*</span></label>

                <select name="github_path" class="modal-input modal-select font-mono" data-github-path>

                    @if ($product?->github_path)

                    <option value="{{ $product->github_path }}" selected>{{ $product->github_path }}</option>

                    @else

                    <option value="">— Scan repo dulu —</option>

                    @endif

                </select>

            </div>

        </div>

        <p class="text-[10px] text-slate-400">Saat simpan, script otomatis di-<strong>pull ke storage lokal</strong> (<code>storage/app/private/scripts/github-{slug}/</code>). Inject pakai folder lokal — lebih stabil.</p>

        <p class="text-[10px] text-slate-400">Loader mendeteksi mode <strong>LOCAL</strong> (script di workspace) vs <strong>REMOTE</strong> (loadstring) — server menyuntikkan <code>_G.LIMEHUB_BASE_URL</code> otomatis.</p>

    </div>



    {{-- Lisensi config --}}

    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">

        <div>

            <label class="modal-label">Access Level <span class="text-red-500">*</span></label>

            <div class="flex gap-2 mt-1.5" role="radiogroup" aria-label="Access level">

                @foreach (['user' => 'User', 'admin' => 'Admin only'] as $val => $lbl)

                <label data-access-option="{{ $val }}"

                    class="access-toggle-{{ $formId }} flex items-center gap-2 px-3 py-2 border cursor-pointer transition-colors flex-1

                    {{ $curAccess === $val ? 'border-violet-400 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-400' : 'border-slate-200 dark:border-slate-700 text-slate-500 hover:border-violet-300' }}">

                    <input type="radio" name="access_level" value="{{ $val }}"

                        {{ $curAccess === $val ? 'checked' : '' }}

                        onchange="ProductForm.toggleAccess('{{ $formId }}', '{{ $val }}')"

                        class="sr-only">

                    <span class="text-xs font-semibold">{{ $lbl }}</span>

                </label>

                @endforeach

            </div>

            <p class="text-[10px] text-slate-400 mt-1">User = lisensi user · Admin only = lisensi admin saja</p>

        </div>

        <div>

            <label class="modal-label">Place IDs (Roblox)</label>

            <input type="text" name="place_ids_raw" placeholder="123456, 789012, ... (kosong = universal)"

                value="{{ old('place_ids_raw', $product?->place_ids ? implode(', ', $product->place_ids) : '') }}"

                class="modal-input font-mono" data-place-ids>

            <p class="text-[10px] text-slate-400 mt-1">Pisahkan dengan koma. Kosong = kompatibel semua game. Saat join map, inject otomatis pakai produk yang Place ID-nya cocok.</p>

            <p class="text-[10px] mt-1 hidden" data-place-hint></p>

        </div>

    </div>



    {{-- Status + Catatan --}}

    <div class="grid grid-cols-2 gap-3">

        <div>

            <label class="modal-label">Status</label>

            <select name="status" class="modal-input modal-select">

                @foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif', 'maintenance' => 'Maintenance'] as $val => $lbl)

                    <option value="{{ $val }}" {{ ($product?->status ?? 'active') === $val ? 'selected' : '' }}>{{ $lbl }}</option>

                @endforeach

            </select>

        </div>

        <div>

            <label class="modal-label">Catatan Internal</label>

            <input type="text" name="notes" placeholder="opsional..."

                value="{{ old('notes', $product?->notes) }}" class="modal-input">

        </div>

    </div>



</div>


