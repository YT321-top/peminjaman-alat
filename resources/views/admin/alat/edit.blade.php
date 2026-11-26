@extends('layouts.app')

@section('title', 'Edit Alat Lab')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <nav class="text-xs text-[#6B7280] mb-1">
                <a href="{{ route('admin.alat.index') }}" class="hover:text-[#E53935] transition">Manajemen Alat</a>
                <span class="mx-1.5">/</span>
                <span class="text-[#1F2937] font-medium">Edit #{{ $alat->id_alat }}</span>
            </nav>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937] tracking-tight">Edit Alat: {{ $alat->nama_alat }}</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-[#1F2937] text-white text-[11px] font-semibold">Mode Edit</span>
            </div>
        </div>
        <a
            href="{{ route('admin.alat.index') }}"
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

            <form method="POST" action="{{ route('admin.alat.update', $alat) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-5">
                    <div class="flex items-center gap-3 pb-4 border-b border-[#F3F4F6]">
                        <span class="w-10 h-10 rounded-xl bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#1F2937]">Informasi Alat Lab</h2>
                            <p class="text-xs text-[#6B7280]">Perbarui nama alat, kategori laboratorium, dan stok tersedia.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="nama_alat" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Nama Alat</label>
                            <input
                                id="nama_alat"
                                type="text"
                                name="nama_alat"
                                value="{{ old('nama_alat', $alat->nama_alat) }}"
                                required
                                maxlength="150"
                                class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                            @error('nama_alat')
                                <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="id_kategori" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Kategori</label>
                            <select
                                id="id_kategori"
                                name="id_kategori"
                                required
                                class="w-full h-10 px-3.5 rounded-xl bg-white border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                                @foreach ($kategoris as $kat)
                                    <option value="{{ $kat->id_kategori }}" {{ (string) old('id_kategori', $alat->id_kategori) === (string) $kat->id_kategori ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                                <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jumlah_tersedia" class="block text-xs font-semibold text-[#1F2937] mb-1.5">Jumlah Stok Tersedia</label>
                            <input
                                id="jumlah_tersedia"
                                type="number"
                                name="jumlah_tersedia"
                                value="{{ old('jumlah_tersedia', $alat->jumlah_tersedia) }}"
                                min="0"
                                step="1"
                                required
                                class="w-full h-10 px-3.5 rounded-xl bg-[#F7F7F8] focus:bg-white border border-[#E5E7EB] text-sm font-semibold text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                            >
                            @error('jumlah_tersedia')
                                <p class="mt-1 text-xs text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @include('admin.alat._illustration_controls')
                </div>

                <div class="px-6 py-4 bg-[#F7F7F8] border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <a
                        href="{{ route('admin.alat.index') }}"
                        class="inline-flex items-center h-9 px-5 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="lp-btn-red h-9 px-6 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Preview Kartu Katalog --}}
        @include('admin.alat._preview_sidebar', ['alat' => $alat])
    </div>
@endsection
