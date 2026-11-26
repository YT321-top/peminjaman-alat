@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <nav class="text-xs text-[#6B7280] mb-1">
                <a href="{{ route('admin.kategori.index') }}" class="hover:text-[#E53935] transition">Manajemen Kategori</a>
                <span class="mx-1.5">/</span>
                <span class="text-[#1F2937] font-medium">Tambah Kategori</span>
            </nav>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937] tracking-tight">Tambah Kategori Baru</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-[#E53935] text-white text-[11px] font-semibold">Data Baru</span>
            </div>
        </div>
        <a
            href="{{ route('admin.kategori.index') }}"
            class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition self-start sm:self-auto"
        >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Card Form Utama --}}
        <div class="lg:col-span-8 lp-card-3d bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden">
            <div class="h-1.5 w-full bg-[#E53935]"></div>

            <form method="POST" action="{{ route('admin.kategori.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="p-6 space-y-5">
                    <div class="flex items-center gap-3 pb-4 border-b border-[#F3F4F6]">
                        <span class="w-10 h-10 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                <line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#1F2937]">Informasi Kategori Laboratorium</h2>
                            <p class="text-xs text-[#6B7280]">Nama kategori digunakan untuk mengelompokkan alat di katalog.</p>
                        </div>
                    </div>

                    <div>
                        <label for="nama_kategori" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Nama Kategori</label>
                        <input
                            id="nama_kategori"
                            type="text"
                            name="nama_kategori"
                            value="{{ old('nama_kategori') }}"
                            required
                            maxlength="100"
                            placeholder="Contoh: Lab Elektronika"
                            class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm text-[#1F2937] placeholder-[#6B7280] focus:border-[#E53935] focus:outline-none transition"
                        >
                        @error('nama_kategori')
                            <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    @include('admin.alat._illustration_controls', [
                        'controlTitle' => 'Ikon / Gambar Kategori',
                        'controlSubtitle' => 'Deteksi otomatis dari nama kategori, pilih ikon SVG manual, atau unggah gambar sendiri.',
                    ])
                </div>

                <div class="px-6 py-4 bg-[#F7F7F8] border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <a
                        href="{{ route('admin.kategori.index') }}"
                        class="inline-flex items-center h-9 px-5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="lp-btn-red h-9 px-6 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                    >
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Preview Kategori --}}
        @include('admin.alat._preview_sidebar', ['previewEntity' => 'kategori'])
    </div>
@endsection
