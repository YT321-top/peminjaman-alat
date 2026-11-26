@extends('layouts.app')

@section('title', 'Peminjaman Saya')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Peminjaman Saya</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Pantau status pengajuan, rincian alat, dan ajukan pengembalian alat lab.</p>
        </div>
        <a
            href="{{ route('peminjam.katalog.index') }}"
            class="inline-flex items-center justify-center h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
        >
            Pinjam Alat Lagi
        </a>
    </div>

    {{-- Tab Filter Status --}}
    @php
        $tabs = [
            'semua' => 'Semua',
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];
    @endphp

    <div class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl px-4 mb-6 overflow-x-auto">
        <div class="flex items-center gap-6">
            @foreach ($tabs as $key => $label)
                @php
                    $isActive = $status === $key;
                    $tabUrl = $key === 'semua'
                        ? route('peminjam.peminjaman-saya.index')
                        : route('peminjam.peminjaman-saya.index', ['status' => $key]);
                @endphp
                <a
                    href="{{ $tabUrl }}"
                    class="py-3.5 text-sm whitespace-nowrap border-b-2 transition flex items-center gap-2 {{ $isActive ? 'border-[#E53935] text-[#1F2937] font-semibold' : 'border-transparent text-[#6B7280] hover:text-[#1F2937] font-medium' }}"
                >
                    <span>{{ $label }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isActive ? 'bg-[#FEF2F2] text-[#E53935]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Daftar Transaksi Peminjaman --}}
    <div class="space-y-4">
        @forelse ($peminjamans as $pinjam)
            @php
                $pengembalian = $pinjam->pengembalian;
                $menungguVerifikasiKembali = $pinjam->status === 'disetujui'
                    && $pengembalian !== null
                    && is_null($pengembalian->id_petugas);
                $bisaKembalikan = $pinjam->status === 'disetujui' && $pengembalian === null;

                $badgeClass = match (true) {
                    $pinjam->status === 'menunggu' => 'bg-[#FFFBEB] text-[#D97706]',
                    $menungguVerifikasiKembali => 'bg-[#FFFBEB] text-[#D97706]',
                    $pinjam->status === 'disetujui' => 'bg-[#EFF6FF] text-[#2563EB]',
                    $pinjam->status === 'selesai' => 'bg-[#F0FDF4] text-[#16A34A]',
                    default => 'bg-[#FEF2F2] text-[#DC2626]',
                };

                $badgeLabel = match (true) {
                    $pinjam->status === 'menunggu' => 'Menunggu Persetujuan',
                    $menungguVerifikasiKembali => 'Menunggu Verifikasi Petugas',
                    $pinjam->status === 'disetujui' => 'Disetujui / Dipinjam',
                    $pinjam->status === 'selesai' => 'Selesai',
                    default => 'Ditolak',
                };

                $totalUnitPinjam = $pinjam->detailPeminjaman->sum('jumlah_pinjam');
            @endphp

            <article style="--d: {{ min($loop->index, 8) * 55 }}ms;" class="lp-card-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
                {{-- Header Kartu Transaksi --}}
                <div class="px-5 py-4 bg-[#F7F7F8] border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span class="text-sm font-bold text-[#1F2937]">#PMJ-{{ $pinjam->id_peminjaman }}</span>
                        <span class="text-xs text-[#6B7280]">
                            Tanggal Pinjam: <strong class="font-semibold text-[#1F2937]">{{ $pinjam->tanggal_pinjam?->format('d/m/Y') }}</strong>
                        </span>
                        @if ($pinjam->petugas)
                            <span class="text-xs text-[#6B7280]">
                                Diproses oleh: <strong class="font-semibold text-[#1F2937]">{{ $pinjam->petugas->username }}</strong>
                            </span>
                        @endif
                    </div>

                    <span class="inline-flex items-center self-start sm:self-auto px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                        {{ $badgeLabel }}
                    </span>
                </div>

                {{-- Rincian Alat --}}
                <div class="p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mb-3">
                        Rincian Alat ({{ $pinjam->detailPeminjaman->count() }} jenis, {{ $totalUnitPinjam }} unit)
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($pinjam->detailPeminjaman as $detail)
                            <div class="lp-sub-item flex items-center justify-between gap-3 p-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB]">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-lg bg-white border border-[#E5E7EB] flex items-center justify-center shrink-0">
                                        <x-alat-illustration
                                            :alat-id="$detail->alat?->id_alat"
                                            :kategori="$detail->alat?->kategori?->nama_kategori ?? ''"
                                            :nama-alat="$detail->alat?->nama_alat ?? ''"
                                            class="lp-mini-svg w-9 h-9"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-[#1F2937] truncate">
                                            {{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}
                                        </p>
                                        <p class="text-[11px] text-[#6B7280] truncate">
                                            {{ $detail->alat?->kategori?->nama_kategori ?? 'Umum' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 rounded-md bg-white border border-[#E5E7EB] text-xs font-semibold text-[#1F2937]">
                                    {{ $detail->jumlah_pinjam }} unit
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Informasi Pengembalian & Denda --}}
                    @if ($pengembalian)
                        <div class="mt-4 pt-4 border-t border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-[#6B7280]">
                                <span>
                                    Tanggal Kembali:
                                    <strong class="font-semibold text-[#1F2937]">{{ $pengembalian->tanggal_kembali?->format('d/m/Y') }}</strong>
                                </span>

                                @if ($pengembalian->id_petugas)
                                    <span>
                                        Penerima:
                                        <strong class="font-semibold text-[#1F2937]">{{ $pengembalian->petugas?->username ?? '-' }}</strong>
                                    </span>
                                    <span>
                                        Denda:
                                        <strong class="font-semibold {{ $pengembalian->denda > 0 ? 'text-[#DC2626]' : 'text-[#16A34A]' }}">
                                            Rp {{ number_format((int) $pengembalian->denda, 0, ',', '.') }}
                                        </strong>
                                    </span>
                                @else
                                    <span class="font-medium text-[#D97706]">
                                        Pengembalian telah diajukan — serahkan alat fisik ke petugas laboratorium untuk verifikasi.
                                    </span>
                                @endif
                            </div>

                            @if ($menungguVerifikasiKembali)
                                <span class="inline-flex items-center self-start sm:self-auto px-3 py-1 rounded-full text-xs font-semibold bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A]">
                                    Menunggu Verifikasi Petugas
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Aksi Pengembalian Alat --}}
                    @if ($bisaKembalikan)
                        <div class="mt-4 pt-4 border-t border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <p class="text-xs text-[#6B7280]">
                                Selesai memakai alat? Ajukan pengembalian lalu serahkan alat ke petugas lab.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('peminjam.peminjaman-saya.kembalikan', $pinjam) }}"
                                data-confirm="Ajukan pengembalian alat?"
                                data-confirm-kind="return"
                                data-confirm-target="#PMJ-{{ $pinjam->id_peminjaman }}"
                                data-confirm-message="Pastikan seluruh unit alat sudah siap diserahkan ke petugas lab."
                                data-confirm-ok="Ya, Kembalikan"
                            >
                                @csrf
                                <button
                                    type="submit"
                                    class="lp-btn-red h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold"
                                >
                                    Kembalikan Alat
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4">
                <x-empty-state
                    icon="clipboard"
                    :title="$status === 'semua' ? 'Belum ada riwayat peminjaman.' : 'Tidak ada peminjaman berstatus ' . ($tabs[$status] ?? $status) . '.'"
                    description="Mulai pilih peralatan laboratorium yang dibutuhkan melalui katalog alat."
                    primary-label="Buka Katalog Alat"
                    :primary-href="route('peminjam.katalog.index')"
                    :secondary-label="$status !== 'semua' ? 'Lihat Semua Status' : null"
                    :secondary-href="$status !== 'semua' ? route('peminjam.peminjaman-saya.index', ['status' => 'semua']) : null"
                />
            </div>
        @endforelse
    </div>

    @if ($peminjamans->hasPages())
        <div class="mt-6">
            {{ $peminjamans->links() }}
        </div>
    @endif
@endsection
