@extends('layouts.app')

@section('title', 'Laporan Peminjaman Alat')

@section('content')
    @php
        $queryExport = [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status' => $status,
        ];
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937]">Laporan Peminjaman Alat</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Rekap transaksi peminjaman, pengembalian, dan denda berdasarkan periode dan status.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a
                href="{{ route('petugas.laporan.cetak', $queryExport) }}"
                target="_blank"
                rel="noopener"
                class="lp-btn-red inline-flex items-center gap-2 h-9 px-4 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                <span>Cetak Laporan</span>
            </a>

            <a
                href="{{ route('petugas.laporan.export-csv', $queryExport) }}"
                class="inline-flex items-center gap-2 h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#E53935] hover:text-[#E53935] text-xs font-semibold text-[#1F2937] transition"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    {{-- Filter Periode & Status --}}
    <section class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('petugas.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-3">
                <label for="tanggal_mulai" class="block text-xs font-semibold text-[#6B7280] mb-1">Tanggal Mulai</label>
                <input
                    id="tanggal_mulai"
                    type="date"
                    name="tanggal_mulai"
                    value="{{ $tanggalMulai }}"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="tanggal_selesai" class="block text-xs font-semibold text-[#6B7280] mb-1">Tanggal Selesai</label>
                <input
                    id="tanggal_selesai"
                    type="date"
                    name="tanggal_selesai"
                    value="{{ $tanggalSelesai }}"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
            </div>

            <div class="lg:col-span-3">
                <label for="status" class="block text-xs font-semibold text-[#6B7280] mb-1">Status Transaksi</label>
                <select
                    id="status"
                    name="status"
                    class="w-full h-9 px-3 rounded-lg bg-[#F7F7F8] border border-[#E5E7EB] text-xs font-medium text-[#1F2937] focus:bg-white focus:border-[#E53935] focus:outline-none transition"
                >
                    <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu" {{ $status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ $status === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <div class="lg:col-span-3 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 h-9 px-4 rounded-lg bg-[#1F2937] hover:bg-[#111827] text-white text-xs font-semibold transition"
                >
                    Terapkan Filter
                </button>
                <a
                    href="{{ route('petugas.laporan.index') }}"
                    class="h-9 px-3 rounded-lg border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-medium text-[#6B7280] hover:text-[#1F2937] inline-flex items-center justify-center transition"
                >
                    Reset
                </a>
            </div>
        </form>
    </section>

    {{-- 4 Kartu Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div style="--d: 0ms;" class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Total Transaksi</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($summary['total_transaksi']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                </svg>
            </div>
        </div>

        <div style="--d: 55ms;" class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Total Alat Dipinjam</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">
                    {{ number_format($summary['total_alat_dipinjam']) }}
                    <span class="text-xs font-medium text-[#6B7280]">unit</span>
                </p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
        </div>

        <div style="--d: 110ms;" class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-[#6B7280]">Transaksi Selesai</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1">{{ number_format($summary['transaksi_selesai']) }}</p>
            </div>
            <div class="lp-icon-box w-10 h-10 rounded-lg bg-[#FEF2F2] text-[#E53935] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
        </div>

        <div style="--d: 165ms;" class="lp-card bg-white border border-[#E5E7EB] rounded-xl p-5 flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-xs font-medium text-[#6B7280]">Total Denda Terkumpul</p>
                <p class="text-2xl font-bold text-[#1F2937] mt-1 truncate">
                    Rp {{ number_format($summary['total_denda'], 0, ',', '.') }}
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
        </div>
    </div>

    {{-- Tabel Laporan Lengkap --}}
    <section style="--d: 210ms;" class="lp-panel-lg bg-white border border-[#E5E7EB] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h2 class="text-sm font-bold text-[#1F2937]">Rincian Transaksi Peminjaman</h2>
            <p class="text-xs text-[#6B7280]">
                Periode {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[11px] font-semibold uppercase tracking-wider text-[#6B7280]">
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Peminjam</th>
                        <th class="px-4 py-3">Tanggal Pinjam</th>
                        <th class="px-4 py-3">Daftar Alat &amp; Jumlah</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Petugas Penyetuju</th>
                        <th class="px-4 py-3">Tanggal Kembali</th>
                        <th class="px-4 py-3 text-right">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse ($peminjamans as $pinjam)
                        @php
                            $badgeClass = match ($pinjam->status) {
                                'menunggu' => 'bg-[#FFFBEB] text-[#D97706]',
                                'disetujui' => 'bg-[#EFF6FF] text-[#2563EB]',
                                'selesai' => 'bg-[#F0FDF4] text-[#16A34A]',
                                default => 'bg-[#FEF2F2] text-[#DC2626]',
                            };

                            $badgeLabel = match ($pinjam->status) {
                                'menunggu' => 'Menunggu',
                                'disetujui' => 'Disetujui',
                                'selesai' => 'Selesai',
                                default => 'Ditolak',
                            };

                            $pengembalian = $pinjam->pengembalian;
                            $sudahSelesai = $pinjam->status === 'selesai' && $pengembalian !== null;
                            $dendaNominal = $sudahSelesai ? (int) $pengembalian->denda : 0;
                        @endphp
                        <tr class="lp-tr-row align-top">
                            <td class="px-4 py-3.5 font-bold text-[#1F2937] whitespace-nowrap">
                                #PMJ-{{ $pinjam->id_peminjaman }}
                            </td>
                            <td class="px-4 py-3.5 text-xs font-semibold text-[#1F2937] whitespace-nowrap">
                                {{ $pinjam->peminjam?->username ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-[#6B7280] whitespace-nowrap">
                                {{ $pinjam->tanggal_pinjam?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3.5 min-w-[220px]">
                                <ul class="space-y-0.5">
                                    @foreach ($pinjam->detailPeminjaman as $detail)
                                        <li class="text-xs text-[#1F2937]">
                                            <span class="font-medium">{{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }}</span>
                                            <span class="text-[#6B7280]">({{ $detail->jumlah_pinjam }} unit)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-[#1F2937] whitespace-nowrap">
                                {{ $pinjam->petugas?->username ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-[#6B7280] whitespace-nowrap">
                                @if ($pengembalian?->tanggal_kembali)
                                    <span class="text-[#1F2937] font-medium">{{ $pengembalian->tanggal_kembali->format('d/m/Y') }}</span>
                                    @if (is_null($pengembalian->id_petugas) && $pinjam->status === 'disetujui')
                                        <span class="block text-[11px] text-[#D97706]">Menunggu verifikasi</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-right whitespace-nowrap font-semibold {{ $dendaNominal > 0 ? 'text-[#DC2626]' : 'text-[#1F2937]' }}">
                                @if ($sudahSelesai)
                                    Rp {{ number_format($dendaNominal, 0, ',', '.') }}
                                @else
                                    <span class="font-normal text-[#6B7280]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-4">
                                <x-empty-state
                                    icon="clipboard"
                                    title="Tidak ada data transaksi pada periode ini."
                                    description="Ubah rentang tanggal atau filter status untuk melihat data lainnya."
                                    secondary-label="Reset Filter Laporan"
                                    :secondary-href="route('petugas.laporan.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($peminjamans->hasPages())
        <div class="mt-6">
            {{ $peminjamans->links() }}
        </div>
    @endif
@endsection
