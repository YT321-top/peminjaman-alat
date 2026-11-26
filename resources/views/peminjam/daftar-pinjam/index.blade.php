@extends('layouts.app')

@section('title', 'Daftar Pinjam')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Daftar Pinjam</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Periksa kembali alat dan jumlah yang akan diajukan peminjaman.</p>
        </div>
        <a
            href="{{ route('peminjam.katalog.index') }}"
            class="inline-flex items-center justify-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
        >
            Lanjut Pilih Alat
        </a>
    </div>

    @if (empty($items))
        <div class="lp-card-lg bg-white border border-[#E5E7EB] rounded-xl p-10 text-center max-w-2xl mx-auto">
            <div class="w-16 h-16 rounded-xl bg-[#F7F7F8] border border-[#E5E7EB] mx-auto flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-[#6B7280]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <h2 class="text-base font-bold text-[#1F2937]">Daftar Pinjam Masih Kosong</h2>
            <p class="text-sm text-[#6B7280] mt-1">Pilih satu atau beberapa alat laboratorium dari katalog untuk mulai mengajukan peminjaman.</p>
            <a
                href="{{ route('peminjam.katalog.index') }}"
                class="mt-5 inline-flex items-center h-10 px-6 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
            >
                Buka Katalog Alat
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Kiri (8 kolom): Daftar Item Alat Terpilih --}}
            <section class="lp-panel-lg lg:col-span-8 bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1F2937]">Alat Terpilih ({{ $totalJenis }} jenis)</h2>
                    <span class="text-xs text-[#6B7280]">Total {{ $totalUnit }} unit</span>
                </div>

                <div class="divide-y divide-[#E5E7EB]">
                    @foreach ($items as $item)
                        @php
                            $alat = $item['alat'];
                            $qty = $item['jumlah_pinjam'];
                            $melebihi = $item['melebihi_stok'];
                        @endphp
                        <div class="lp-sub-item p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <a
                                    href="{{ route('peminjam.katalog.show', $alat) }}"
                                    class="w-16 h-16 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] flex items-center justify-center shrink-0"
                                >
                                    <x-alat-illustration
                                        :alat-id="$alat->id_alat"
                                        :kategori="$alat->kategori?->nama_kategori ?? ''"
                                        :nama-alat="$alat->nama_alat"
                                        class="lp-mini-svg w-12 h-12"
                                    />
                                </a>
                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-wider text-[#6B7280]">
                                        {{ $alat->kategori?->nama_kategori ?? 'Umum' }}
                                    </p>
                                    <a
                                        href="{{ route('peminjam.katalog.show', $alat) }}"
                                        class="text-sm font-semibold text-[#1F2937] hover:text-[#E53935] transition block truncate mt-0.5"
                                    >
                                        {{ $alat->nama_alat }}
                                    </a>
                                    @if ($melebihi)
                                        <p class="text-xs font-medium text-[#DC2626] mt-1">
                                            Stok tersedia saat ini hanya {{ $alat->jumlah_tersedia }} unit. Sesuaikan jumlah sebelum mengajukan.
                                        </p>
                                    @else
                                        <p class="text-xs text-[#16A34A] mt-1">
                                            Stok tersedia: {{ $alat->jumlah_tersedia }} unit
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                {{-- Stepper - 1 + --}}
                                <div class="flex items-center border border-[#E5E7EB] rounded-lg bg-white overflow-hidden">
                                    <form method="POST" action="{{ route('peminjam.daftar-pinjam.update', $alat) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="jumlah" value="{{ max(1, $qty - 1) }}">
                                        <button
                                            type="submit"
                                            @disabled($qty <= 1)
                                            class="w-8 h-9 flex items-center justify-center text-sm font-bold text-[#1F2937] hover:bg-[#F7F7F8] disabled:text-[#9CA3AF] disabled:cursor-not-allowed transition"
                                            aria-label="Kurangi jumlah"
                                        >
                                            -
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('peminjam.daftar-pinjam.update', $alat) }}" class="flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="number"
                                            name="jumlah"
                                            value="{{ $qty }}"
                                            min="1"
                                            max="{{ max(1, $alat->jumlah_tersedia) }}"
                                            onchange="this.form.submit()"
                                            aria-label="Jumlah {{ $alat->nama_alat }}"
                                            class="w-12 h-9 text-center text-xs font-semibold text-[#1F2937] border-x border-[#E5E7EB] focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        >
                                    </form>

                                    <form method="POST" action="{{ route('peminjam.daftar-pinjam.update', $alat) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="jumlah" value="{{ $qty + 1 }}">
                                        <button
                                            type="submit"
                                            @disabled($qty >= $alat->jumlah_tersedia)
                                            class="w-8 h-9 flex items-center justify-center text-sm font-bold text-[#1F2937] hover:bg-[#F7F7F8] disabled:text-[#9CA3AF] disabled:cursor-not-allowed transition"
                                            aria-label="Tambah jumlah"
                                        >
                                            +
                                        </button>
                                    </form>
                                </div>

                                {{-- Hapus Item --}}
                                <form method="POST" action="{{ route('peminjam.daftar-pinjam.hapus', $alat) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="h-9 px-3 rounded-lg border border-[#E5E7EB] text-xs font-medium text-[#6B7280] hover:border-[#DC2626] hover:text-[#DC2626] transition"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Kanan (4 kolom): Panel Ringkasan & Submit Pengajuan --}}
            <aside style="--d: 90ms;" class="lp-card-lg lg:col-span-4 bg-white border border-[#E5E7EB] rounded-xl p-5">
                <h2 class="text-sm font-bold text-[#1F2937] pb-3 border-b border-[#E5E7EB]">Ringkasan Pengajuan</h2>

                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-[#6B7280]">Total Jenis Alat</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">{{ $totalJenis }} jenis</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-[#6B7280]">Total Unit Dipinjam</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">{{ $totalUnit }} unit</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-[#6B7280]">Batas Pinjam Standar</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">3 hari</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('peminjam.daftar-pinjam.ajukan') }}" class="mt-5 pt-4 border-t border-[#E5E7EB] space-y-4">
                    @csrf

                    <div>
                        <label for="tanggal_pinjam" class="block text-xs font-semibold text-[#1F2937] mb-1.5">
                            Tanggal Pinjam
                        </label>
                        <input
                            id="tanggal_pinjam"
                            type="date"
                            name="tanggal_pinjam"
                            value="{{ old('tanggal_pinjam', now()->toDateString()) }}"
                            min="{{ now()->toDateString() }}"
                            required
                            class="w-full h-10 px-3 rounded-lg border border-[#E5E7EB] text-sm text-[#1F2937] focus:border-[#E53935] focus:outline-none transition"
                        >
                        @error('tanggal_pinjam')
                            <p class="text-xs text-[#DC2626] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($adaMelebihiStok)
                        <p class="text-xs text-[#DC2626] bg-[#FEF2F2] border border-[#FECACA] rounded-lg px-3 py-2">
                            Ada item yang melebihi stok tersedia. Sesuaikan jumlah terlebih dahulu.
                        </p>
                    @endif

                    <button
                        type="submit"
                        @disabled($adaMelebihiStok)
                        class="lp-btn-red w-full h-10 rounded-full text-xs font-semibold {{ $adaMelebihiStok ? 'bg-[#E5E7EB] text-[#6B7280] cursor-not-allowed' : 'bg-[#E53935] hover:bg-[#C62828] text-white' }}"
                    >
                        Ajukan Peminjaman
                    </button>

                    <a
                        href="{{ route('peminjam.katalog.index') }}"
                        class="w-full h-10 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] flex items-center justify-center transition"
                    >
                        Lanjut Pilih Alat
                    </a>
                </form>
            </aside>
        </div>
    @endif
@endsection
