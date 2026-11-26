@extends('layouts.app')

@section('title', $alat->nama_alat)

@section('content')
    @php
        $kodeAlat = 'ALT-' . str_pad((string) $alat->id_alat, 3, '0', STR_PAD_LEFT);
        $habis = $alat->jumlah_tersedia <= 0;
        $tidakBisaTambah = $habis || $sisaBisaDitambah <= 0;
    @endphp

    {{-- Breadcrumb Ringkas --}}
    <nav class="mb-5 text-xs text-[#6B7280] flex flex-wrap items-center gap-2" aria-label="Breadcrumb">
        <a href="{{ route('peminjam.katalog.index') }}" class="hover:text-[#E53935] transition">Katalog</a>
        <span>/</span>
        @if ($alat->kategori)
            <a
                href="{{ route('peminjam.katalog.index', ['kategori' => $alat->id_kategori]) }}"
                class="hover:text-[#E53935] transition"
            >
                {{ $alat->kategori->nama_kategori }}
            </a>
            <span>/</span>
        @endif
        <span class="font-semibold text-[#1F2937]">{{ $alat->nama_alat }}</span>
    </nav>

    {{-- Layout 3 Bagian Detail Alat (Referensi TMK Detail) --}}
    <section class="lp-card-lg bg-white border border-[#E5E7EB] rounded-xl p-6 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Kiri (5 kolom): Visual Ilustrasi SVG Besar --}}
            <div class="lg:col-span-5">
                <div class="lp-illu-stage lp-illu-stage--soft bg-[#F7F7F8] border border-[#E5E7EB] rounded-xl h-72 sm:h-80 flex flex-col items-center justify-center p-8 relative">
                    <span class="absolute top-3.5 left-3.5 z-10 px-2.5 py-1 rounded-md bg-white border border-[#E5E7EB] text-[11px] font-semibold text-[#6B7280]">
                        {{ $kodeAlat }}
                    </span>
                    <x-alat-illustration
                        :alat-id="$alat->id_alat"
                        :kategori="$alat->kategori?->nama_kategori ?? ''"
                        :nama-alat="$alat->nama_alat"
                        class="lp-illu-svg w-44 h-44"
                    />
                </div>
            </div>

            {{-- Tengah (4 kolom): Info Utama & Aturan Peminjaman --}}
            <div class="lg:col-span-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-[#F7F7F8] border border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                    {{ $alat->kategori?->nama_kategori ?? 'Umum' }}
                </span>

                <h1 class="text-2xl font-bold text-[#1F2937] mt-2.5 leading-snug">
                    {{ $alat->nama_alat }}
                </h1>

                <div class="mt-3">
                    @if ($habis)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#FEF2F2] text-[#DC2626]">
                            Stok Habis
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#F0FDF4] text-[#16A34A]">
                            Tersedia: {{ $alat->jumlah_tersedia }} unit
                        </span>
                    @endif
                </div>

                <dl class="mt-6 border-y border-[#E5E7EB] divide-y divide-[#E5E7EB] text-sm">
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs text-[#6B7280]">Kode Alat</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">{{ $kodeAlat }}</dd>
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs text-[#6B7280]">Kategori Lab</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">{{ $alat->kategori?->nama_kategori ?? '-' }}</dd>
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs text-[#6B7280]">Batas Pinjam Standar</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">3 hari sejak tanggal pinjam</dd>
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs text-[#6B7280]">Tarif Denda Terlambat</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">Rp 2.000 / hari</dd>
                    </div>
                    <div class="py-3 flex items-center justify-between gap-4">
                        <dt class="text-xs text-[#6B7280]">Proses Persetujuan</dt>
                        <dd class="text-xs font-semibold text-[#1F2937]">Verifikasi Petugas Lab</dd>
                    </div>
                </dl>
            </div>

            {{-- Kanan (3 kolom): Panel Aksi Pinjam --}}
            <div class="lg:col-span-3">
                <div class="bg-[#F7F7F8] border border-[#E5E7EB] rounded-xl p-5">
                    <p class="text-xs font-medium text-[#6B7280]">Ketersediaan Saat Ini</p>
                    <p class="text-xl font-bold text-[#1F2937] mt-1">
                        {{ $alat->jumlah_tersedia }} <span class="text-sm font-medium text-[#6B7280]">Unit Tersedia</span>
                    </p>

                    @if ($qtyDiKeranjang > 0)
                        <p class="mt-2 px-3 py-2 rounded-lg bg-white border border-[#E5E7EB] text-xs text-[#1F2937]">
                            Sudah ada <span class="font-semibold">{{ $qtyDiKeranjang }} unit</span> alat ini di Daftar Pinjam Anda.
                        </p>
                    @endif

                    <form method="POST" action="{{ route('peminjam.daftar-pinjam.tambah') }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="id_alat" value="{{ $alat->id_alat }}">

                        <div>
                            <label for="qty-input" class="block text-xs font-semibold text-[#1F2937] mb-2">Jumlah Pinjam</label>
                            <div class="flex items-center border border-[#E5E7EB] rounded-lg bg-white overflow-hidden">
                                <button
                                    type="button"
                                    id="qty-minus"
                                    @disabled($tidakBisaTambah)
                                    class="w-10 h-10 flex items-center justify-center text-base font-bold text-[#1F2937] hover:bg-[#F7F7F8] disabled:text-[#9CA3AF] disabled:cursor-not-allowed transition"
                                    aria-label="Kurangi jumlah"
                                >
                                    -
                                </button>
                                <input
                                    id="qty-input"
                                    type="number"
                                    name="jumlah"
                                    value="{{ $tidakBisaTambah ? 0 : 1 }}"
                                    min="{{ $tidakBisaTambah ? 0 : 1 }}"
                                    max="{{ $sisaBisaDitambah }}"
                                    @disabled($tidakBisaTambah)
                                    class="w-full h-10 text-center text-sm font-semibold text-[#1F2937] border-x border-[#E5E7EB] focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                >
                                <button
                                    type="button"
                                    id="qty-plus"
                                    @disabled($tidakBisaTambah)
                                    class="w-10 h-10 flex items-center justify-center text-base font-bold text-[#1F2937] hover:bg-[#F7F7F8] disabled:text-[#9CA3AF] disabled:cursor-not-allowed transition"
                                    aria-label="Tambah jumlah"
                                >
                                    +
                                </button>
                            </div>
                            @if (!$habis)
                                <p class="text-[11px] text-[#6B7280] mt-1.5">
                                    Maksimal dapat ditambahkan: {{ $sisaBisaDitambah }} unit
                                </p>
                            @endif
                        </div>

                        <button
                            type="submit"
                            @disabled($tidakBisaTambah)
                            class="lp-btn-red w-full h-10 rounded-full text-xs font-semibold {{ $tidakBisaTambah ? 'bg-[#E5E7EB] text-[#6B7280] cursor-not-allowed' : 'bg-[#E53935] hover:bg-[#C62828] text-white' }}"
                        >
                            @if ($habis)
                                Stok Habis
                            @elseif ($sisaBisaDitambah <= 0)
                                Maksimal Stok di Daftar Pinjam
                            @else
                                Tambah ke Daftar Pinjam
                            @endif
                        </button>
                    </form>

                    <a
                        href="{{ route('peminjam.daftar-pinjam.index') }}"
                        class="mt-3 w-full h-10 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] flex items-center justify-center transition"
                    >
                        Lihat Daftar Pinjam
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Bagian Bawah: Alat Lain di Kategori Ini (Maksimal 4 Alat) --}}
    @if ($alatTerkait->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-[#1F2937]">Alat Lain di Kategori Ini</h2>
                @if ($alat->kategori)
                    <a
                        href="{{ route('peminjam.katalog.index', ['kategori' => $alat->id_kategori]) }}"
                        class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition"
                    >
                        Lihat Semua {{ $alat->kategori->nama_kategori }}
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($alatTerkait as $item)
                    <article style="--d: {{ $loop->index * 55 }}ms;" class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-4 flex flex-col justify-between">
                        <div>
                            <a
                                href="{{ route('peminjam.katalog.show', $item) }}"
                                class="lp-illu-stage block bg-[#F7F7F8] rounded-lg h-32 flex items-center justify-center mb-3"
                            >
                                <x-alat-illustration
                                    :alat-id="$item->id_alat"
                                    :kategori="$item->kategori?->nama_kategori ?? ''"
                                    :nama-alat="$item->nama_alat"
                                    class="lp-illu-svg w-16 h-16"
                                />
                            </a>
                            <p class="text-[11px] text-[#6B7280] truncate">{{ $item->kategori?->nama_kategori ?? 'Umum' }}</p>
                            <h3 class="mt-0.5">
                                <a
                                    href="{{ route('peminjam.katalog.show', $item) }}"
                                    class="text-sm font-semibold text-[#1F2937] hover:text-[#E53935] transition line-clamp-2"
                                >
                                    {{ $item->nama_alat }}
                                </a>
                            </h3>
                        </div>

                        <div class="pt-3 mt-3 border-t border-[#E5E7EB] flex items-center justify-between">
                            @if ($item->jumlah_tersedia > 0)
                                <span class="text-xs font-medium text-[#16A34A]">Stok: {{ $item->jumlah_tersedia }} unit</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FEF2F2] text-[#DC2626]">Stok Habis</span>
                            @endif

                            <a
                                href="{{ route('peminjam.katalog.show', $item) }}"
                                class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition"
                            >
                                Detail
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @include('peminjam.partials.added-modal')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('qty-input');
            const btnMinus = document.getElementById('qty-minus');
            const btnPlus = document.getElementById('qty-plus');

            if (!input || input.disabled) return;

            const min = parseInt(input.min || '1', 10);
            const max = parseInt(input.max || '1', 10);

            function clampValue() {
                let val = parseInt(input.value, 10);
                if (isNaN(val) || val < min) val = min;
                if (val > max) val = max;
                input.value = val;
            }

            btnMinus.addEventListener('click', function () {
                input.value = Math.max(min, (parseInt(input.value, 10) || min) - 1);
            });

            btnPlus.addEventListener('click', function () {
                input.value = Math.min(max, (parseInt(input.value, 10) || min) + 1);
            });

            input.addEventListener('change', clampValue);
        });
    </script>
@endpush
