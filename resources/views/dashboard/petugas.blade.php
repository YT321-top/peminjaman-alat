@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Dashboard Petugas</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Pantau antrean persetujuan peminjaman, verifikasi pengembalian, dan laporan alat lab.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('petugas.persetujuan.index') }}"
                class="lp-btn-red inline-flex items-center h-9 px-4 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
            >
                Kelola Persetujuan
            </a>
            <a
                href="{{ route('petugas.pengembalian.index') }}"
                class="inline-flex items-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition"
            >
                Pantau Pengembalian
            </a>
            <a
                href="{{ route('petugas.laporan.index') }}"
                class="inline-flex items-center h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition"
            >
                Laporan
            </a>
        </div>
    </div>

    {{-- 4 Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a
            href="{{ route('petugas.persetujuan.index', ['status' => 'menunggu']) }}"
            style="--d: 0ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Pengajuan Menunggu</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['antrean_menunggu']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <polyline points="12 7 12 12 15 14"/>
                </svg>
            </div>
        </a>

        <a
            href="{{ route('petugas.pengembalian.index', ['tab' => 'dipinjam']) }}"
            style="--d: 55ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Sedang Dipinjam</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['sedang_dipinjam']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                </svg>
            </div>
        </a>

        <a
            href="{{ route('petugas.pengembalian.index', ['tab' => 'verifikasi']) }}"
            style="--d: 110ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Menunggu Verifikasi Kembali</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($stats['menunggu_verifikasi']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 14 4 9 9 4"/>
                    <path d="M20 20v-7a4 4 0 0 0-4-4H4"/>
                </svg>
            </div>
        </a>

        <a
            href="{{ route('petugas.laporan.index') }}"
            style="--d: 165ms;"
            class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between"
        >
            <div class="min-w-0 pr-2">
                <p class="text-xs font-medium text-[#6B7280]">Total Denda Bulan Ini</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1 truncate">
                    Rp {{ number_format($stats['total_denda_bulan_ini'], 0, ',', '.') }}
                </p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="8" y1="13" x2="16" y2="13"/>
                    <line x1="8" y1="17" x2="13" y2="17"/>
                </svg>
            </div>
        </a>
    </div>

    {{-- Pengembalian Menunggu Verifikasi (jika ada) --}}
    @if ($menungguVerifikasiKembali->isNotEmpty())
        <section style="--d: 200ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-[#1F2937]">Pengembalian Menunggu Verifikasi Fisik</h2>
                    <p class="text-xs text-[#6B7280] mt-0.5">Peminjam telah mengajukan pengembalian dan menunggu konfirmasi penerimaan alat.</p>
                </div>
                <a href="{{ route('petugas.pengembalian.index', ['tab' => 'verifikasi']) }}" class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition shrink-0">
                    Buka Menu Pengembalian
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Peminjam</th>
                            <th class="px-5 py-3">Tanggal Pinjam</th>
                            <th class="px-5 py-3">Diajukan Kembali</th>
                            <th class="px-5 py-3">Rincian Alat</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-sm">
                        @foreach ($menungguVerifikasiKembali as $item)
                            <tr class="lp-tr-row">
                                <td class="px-5 py-3.5 font-semibold text-[#1F2937] whitespace-nowrap">
                                    #PMJ-{{ $item->id_peminjaman }}
                                </td>
                                <td class="px-5 py-3.5 font-medium text-[#1F2937] whitespace-nowrap">
                                    {{ $item->peminjam?->username ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-[#6B7280] whitespace-nowrap">
                                    {{ $item->tanggal_pinjam?->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-[#D97706] font-medium whitespace-nowrap">
                                    {{ $item->pengembalian?->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <ul class="space-y-1">
                                        @foreach ($item->detailPeminjaman as $detail)
                                            <li class="text-xs text-[#1F2937]">
                                                <span class="font-medium">{{ $detail->alat?->nama_alat ?? '-' }}</span>
                                                <span class="text-[#6B7280]">({{ $detail->jumlah_pinjam }} unit)</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('petugas.pengembalian.index', ['tab' => 'verifikasi', 'q' => '#PMJ-' . $item->id_peminjaman]) }}"
                                        class="inline-flex items-center h-8 px-3.5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                                    >
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Tabel Antrean Peminjaman Menunggu --}}
    <section style="--d: 240ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
            <h2 class="text-sm font-bold text-[#1F2937]">Antrean Peminjaman Menunggu</h2>
            <a href="{{ route('petugas.persetujuan.index', ['status' => 'menunggu']) }}" class="text-xs font-semibold text-[#E53935] hover:text-[#C62828] transition">
                Lihat Semua Persetujuan
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Peminjam</th>
                        <th class="px-5 py-3">Tanggal Pinjam</th>
                        <th class="px-5 py-3">Rincian Alat</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($antreanMenunggu as $pinjam)
                        <tr class="lp-tr-row">
                            <td class="px-5 py-3.5 font-semibold text-[#1F2937] whitespace-nowrap">
                                #PMJ-{{ $pinjam->id_peminjaman }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-[#1F2937] whitespace-nowrap">
                                {{ $pinjam->peminjam?->username ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $pinjam->tanggal_pinjam?->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <ul class="space-y-1">
                                    @foreach ($pinjam->detailPeminjaman as $detail)
                                        @php
                                            $stok = (int) ($detail->alat?->jumlah_tersedia ?? 0);
                                            $stokKurang = $detail->jumlah_pinjam > $stok;
                                        @endphp
                                        <li class="text-xs text-[#1F2937]">
                                            <span class="font-medium">{{ $detail->alat?->nama_alat ?? '-' }}</span>
                                            <span class="{{ $stokKurang ? 'text-[#DC2626] font-semibold' : 'text-[#6B7280]' }}">
                                                ({{ $detail->jumlah_pinjam }} unit — stok: {{ $stok }}{{ $stokKurang ? ', stok kurang' : '' }})
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFFBEB] text-[#D97706]">
                                    Menunggu
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a
                                    href="{{ route('petugas.persetujuan.index', ['status' => 'menunggu', 'q' => '#PMJ-' . $pinjam->id_peminjaman]) }}"
                                    class="inline-flex items-center h-8 px-3.5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
                                >
                                    Tinjau
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-[#6B7280]">
                                Tidak ada pengajuan peminjaman yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
