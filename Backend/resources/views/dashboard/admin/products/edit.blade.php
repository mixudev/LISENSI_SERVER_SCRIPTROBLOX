@extends('dashboard.admin.layouts.main')
@section('title', 'Edit Produk')
@section('content')

{{-- ── BREADCRUMB ── --}}
<div class="flex items-center gap-2 mb-5 text-xs">
    <a href="{{ route('admin.products.index') }}"
        class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Produk
    </a>
    <svg class="w-3 h-3 text-slate-300 dark:text-slate-600" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none"/>
    </svg>
    <span class="text-slate-700 dark:text-slate-300 font-medium truncate max-w-[200px]">{{ $product->name }}</span>
</div>

{{-- ── PAGE HEADER ── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Edit Produk</h2>
        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $product->slug }}</p>
    </div>
</div>

{{-- ── FORM CARD ── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 max-w-2xl">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Informasi Produk</h3>
    </div>
    <div class="p-6">
        @php
            $input = 'w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500';
            $label = 'block text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-1.5';
        @endphp

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data"
              class="grid grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div class="col-span-2">
                <label class="{{ $label }}">Nama Produk <span class="text-red-500 normal-case font-normal">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="{{ $input }}">
            </div>
            <div class="col-span-2">
                <label class="{{ $label }}">Deskripsi</label>
                <textarea name="description" rows="3" class="{{ $input }}">{{ old('description', $product->description) }}</textarea>
            </div>
            <div>
                <label class="{{ $label }}">Versi <span class="text-red-500 normal-case font-normal">*</span></label>
                <input type="text" name="version" value="{{ old('version', $product->version) }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Durasi Lisensi (hari) <span class="text-red-500 normal-case font-normal">*</span></label>
                <input type="number" name="license_duration_days"
                    value="{{ old('license_duration_days', $product->license_duration_days) }}" min="0" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Max Reset HWID <span class="text-red-500 normal-case font-normal">*</span></label>
                <input type="number" name="max_hwid_resets"
                    value="{{ old('max_hwid_resets', $product->max_hwid_resets) }}" min="0" max="99" required class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Interval Reset HWID (hari)</label>
                <input type="number" name="hwid_reset_interval_days"
                    value="{{ old('hwid_reset_interval_days', $product->hwid_reset_interval_days) }}" min="0" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Status</label>
                <select name="status" class="{{ $input }}">
                    @php $currentStatus = $product->status instanceof \App\Enums\ProductStatus ? $product->status->value : $product->status; @endphp
                    @foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif', 'maintenance' => 'Maintenance'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('status', $currentStatus) === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="currency" value="{{ old('currency', $product->currency ?? 'IDR') }}">
            <div class="col-span-2">
                <label class="{{ $label }}">Ganti File Script</label>
                <input type="file" name="script_file"
                    class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-violet-50 dark:file:bg-violet-900/20 file:text-violet-700 dark:file:text-violet-400 hover:file:bg-violet-100">
                @if ($product->script_path)
                    <p class="text-[10px] text-slate-400 mt-1 font-mono">File saat ini: {{ $product->script_path }}</p>
                @endif
            </div>

            <div class="col-span-2 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 border border-violet-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.products.index') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
