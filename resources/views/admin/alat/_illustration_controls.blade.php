@php
    $initialMode = old('illustration_mode', $currentOverride['mode'] ?? 'auto');
    if (!in_array($initialMode, ['auto', 'manual_icon', 'upload'], true)) {
        $initialMode = 'auto';
    }
    $initialIcon = old('illustration_icon', $currentOverride['type'] ?? 'erlenmeyer');
    $existingUploadUrl = ($currentResolved['mode'] ?? '') === 'upload' ? ($currentResolved['url'] ?? null) : null;
    $existingUploadFile = $currentOverride['file'] ?? null;
    $headingTitle = $controlTitle ?? 'Ilustrasi Kartu Katalog';
    $headingSubtitle = $controlSubtitle ?? 'Deteksi otomatis, pilih ikon SVG manual, atau unggah gambar sendiri.';
@endphp

<div class="pt-4 border-t border-[#F3F4F6] space-y-3">
    <div class="flex items-center justify-between gap-2">
        <div>
            <span class="block text-xs font-semibold text-[#1F2937]">{{ $headingTitle }}</span>
            <p class="text-[11px] text-[#6B7280]">{{ $headingSubtitle }}</p>
        </div>
    </div>

    <input type="hidden" id="illustration_mode" name="illustration_mode" value="{{ $initialMode }}">
    <input type="hidden" id="illustration_icon" name="illustration_icon" value="{{ $initialIcon }}">

    {{-- Tab Mode 3 Opsi --}}
    <div class="grid grid-cols-3 gap-1 p-1 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB]">
        <button type="button" data-illu-mode="auto" class="illu-mode-btn py-1.5 px-2 rounded-lg text-xs transition cursor-pointer">
            Otomatis
        </button>
        <button type="button" data-illu-mode="manual_icon" class="illu-mode-btn py-1.5 px-2 rounded-lg text-xs transition cursor-pointer">
            Pilih Ikon ({{ count($illustrationConfig['icons'] ?? []) }})
        </button>
        <button type="button" data-illu-mode="upload" class="illu-mode-btn py-1.5 px-2 rounded-lg text-xs transition cursor-pointer">
            Upload Gambar
        </button>
    </div>

    {{-- Panel Pilih Ikon Manual --}}
    <div id="illu-panel-manual" class="{{ $initialMode === 'manual_icon' ? '' : 'hidden' }} space-y-2.5 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB] p-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap gap-1">
                <button type="button" data-illu-grp="all" class="illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition">Semua</button>
                <button type="button" data-illu-grp="kimia" class="illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition">Kimia</button>
                <button type="button" data-illu-grp="fisika" class="illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition">Fisika</button>
                <button type="button" data-illu-grp="biologi" class="illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition">Biologi</button>
                <button type="button" data-illu-grp="komputer" class="illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition">IT</button>
                <button type="button" data-illu-grp="multimedia" class="illu-grp-btn px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition">Media</button>
            </div>
            <input
                id="illu-icon-search"
                type="text"
                placeholder="Cari ikon..."
                class="h-7 w-32 sm:w-36 px-2.5 rounded-lg border border-[#E5E7EB] bg-white text-xs text-[#1F2937] placeholder-[#6B7280] focus:border-[#E53935] focus:outline-none"
            >
        </div>

        <div id="illu-icon-grid" class="grid grid-cols-4 sm:grid-cols-6 gap-2 max-h-44 overflow-y-auto pr-1"></div>
    </div>

    {{-- Panel Upload Gambar Manual --}}
    <div id="illu-panel-upload" class="{{ $initialMode === 'upload' ? '' : 'hidden' }} space-y-2">
        <label class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-dashed border-[#D1D5DB] bg-[#F7F7F8] hover:border-[#E53935] cursor-pointer transition">
            <div class="min-w-0">
                <p id="illu-upload-label" class="text-xs font-semibold text-[#1F2937] truncate">
                    {{ $existingUploadFile ? 'Gambar tersimpan: ' . $existingUploadFile . ' (klik untuk ganti)' : 'Pilih file PNG / JPG / WebP (maks. 2 MB)' }}
                </p>
                <p class="text-[11px] text-[#6B7280]">Gambar disimpan lokal tanpa mengubah struktur tabel database.</p>
            </div>
            <span class="px-3 py-1.5 rounded-full bg-[#1F2937] text-white text-xs font-semibold shrink-0">Pilih File</span>
            <input
                id="illustration_file"
                name="illustration_file"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="hidden"
            >
        </label>
    </div>

    @error('illustration_mode')
        <p class="text-xs text-[#DC2626]">{{ $message }}</p>
    @enderror
    @error('illustration_icon')
        <p class="text-xs text-[#DC2626]">{{ $message }}</p>
    @enderror
    @error('illustration_file')
        <p class="text-xs text-[#DC2626]">{{ $message }}</p>
    @enderror
</div>
