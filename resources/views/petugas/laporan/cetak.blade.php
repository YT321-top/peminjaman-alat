<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Peminjaman Alat — LabPinjam</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        @media print {
            html, body {
                background: #FFFFFF !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body class="bg-[#F7F7F8] text-[#1F2937] antialiased py-6 px-4 print:bg-white print:p-0">
    {{-- Toolbar Aksi Layar (Tersembunyi Saat Dicetak) --}}
    <div class="print:hidden max-w-[210mm] mx-auto mb-4 flex flex-wrap items-center justify-between gap-3">
        <a
            href="{{ route('petugas.laporan.index', ['tanggal_mulai' => $tanggalMulai, 'tanggal_selesai' => $tanggalSelesai, 'status' => $status]) }}"
            class="inline-flex items-center gap-2 h-9 px-4 rounded-full border border-[#E5E7EB] bg-white hover:border-[#1F2937] text-xs font-semibold text-[#1F2937] transition"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            <span>Kembali ke Laporan</span>
        </a>

        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center gap-2 h-9 px-5 rounded-full bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-semibold transition"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    {{-- Lembar Cetak A4 --}}
    <main class="max-w-[210mm] mx-auto bg-white border border-[#E5E7EB] p-8 print:max-w-none print:border-0 print:p-0">
        {{-- Kop Resmi Laporan --}}
        <header class="text-center pb-4 border-b-2 border-[#1F2937]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#6B7280]">
                Laboratorium Sekolah — LabPinjam
            </p>
            <h1 class="text-lg font-extrabold tracking-tight text-[#1F2937] uppercase mt-1">
                Laporan Peminjaman Alat Laboratorium Sekolah
            </h1>
            <p class="text-xs text-[#6B7280] mt-1">
                Periode: <strong class="font-semibold text-[#1F2937]">{{ $periodeLabel }}</strong>
                <span class="mx-1.5">|</span>
                Filter Status: <strong class="font-semibold text-[#1F2937]">{{ $statusLabel }}</strong>
            </p>
        </header>

        {{-- Metadata Cetak & Ringkasan --}}
        <div class="mt-4 mb-5 flex flex-wrap items-center justify-between gap-2 text-[11px] text-[#6B7280]">
            <span>Dicetak pada: <strong class="text-[#1F2937]">{{ $waktuCetakLabel }}</strong></span>
            <span>Petugas Pencetak: <strong class="text-[#1F2937]">{{ auth()->user()->username }}</strong></span>
        </div>

        <div class="grid grid-cols-4 gap-3 mb-5">
            <div class="border border-[#1F2937] px-3 py-2">
                <p class="text-[10px] uppercase tracking-wider text-[#6B7280]">Total Transaksi</p>
                <p class="text-sm font-bold text-[#1F2937] mt-0.5">{{ number_format($summary['total_transaksi']) }}</p>
            </div>
            <div class="border border-[#1F2937] px-3 py-2">
                <p class="text-[10px] uppercase tracking-wider text-[#6B7280]">Total Alat Dipinjam</p>
                <p class="text-sm font-bold text-[#1F2937] mt-0.5">{{ number_format($summary['total_alat_dipinjam']) }} unit</p>
            </div>
            <div class="border border-[#1F2937] px-3 py-2">
                <p class="text-[10px] uppercase tracking-wider text-[#6B7280]">Transaksi Selesai</p>
                <p class="text-sm font-bold text-[#1F2937] mt-0.5">{{ number_format($summary['transaksi_selesai']) }}</p>
            </div>
            <div class="border border-[#1F2937] px-3 py-2">
                <p class="text-[10px] uppercase tracking-wider text-[#6B7280]">Total Denda</p>
                <p class="text-sm font-bold text-[#1F2937] mt-0.5">Rp {{ number_format($summary['total_denda'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Tabel Laporan Ber-border Hitam Tipis --}}
        <table class="w-full border-collapse border border-[#1F2937] text-[11px]">
            <thead>
                <tr class="bg-[#F7F7F8] text-[#1F2937]">
                    <th class="border border-[#1F2937] px-2 py-2 text-center w-8">No</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-left whitespace-nowrap">Kode</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-left">Peminjam</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-center whitespace-nowrap">Tgl Pinjam</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-left">Daftar Alat &amp; Jumlah</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-center">Status</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-left">Petugas</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-center whitespace-nowrap">Tgl Kembali</th>
                    <th class="border border-[#1F2937] px-2 py-2 text-right whitespace-nowrap">Denda</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($peminjamans as $index => $pinjam)
                    @php
                        $statusText = match ($pinjam->status) {
                            'menunggu' => 'Menunggu',
                            'disetujui' => 'Disetujui',
                            'selesai' => 'Selesai',
                            default => 'Ditolak',
                        };
                        $pengembalian = $pinjam->pengembalian;
                        $sudahSelesai = $pinjam->status === 'selesai' && $pengembalian !== null;
                    @endphp
                    <tr class="align-top">
                        <td class="border border-[#1F2937] px-2 py-1.5 text-center">{{ $index + 1 }}</td>
                        <td class="border border-[#1F2937] px-2 py-1.5 font-semibold whitespace-nowrap">
                            #PMJ-{{ $pinjam->id_peminjaman }}
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5">
                            {{ $pinjam->peminjam?->username ?? '-' }}
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5 text-center whitespace-nowrap">
                            {{ $pinjam->tanggal_pinjam?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5">
                            <ul class="space-y-0.5">
                                @foreach ($pinjam->detailPeminjaman as $detail)
                                    <li>{{ $detail->alat?->nama_alat ?? 'Alat tidak ditemukan' }} ({{ $detail->jumlah_pinjam }} unit)</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5 text-center whitespace-nowrap">
                            {{ $statusText }}
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5">
                            {{ $pinjam->petugas?->username ?? '-' }}
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5 text-center whitespace-nowrap">
                            {{ $pengembalian?->tanggal_kembali?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="border border-[#1F2937] px-2 py-1.5 text-right whitespace-nowrap">
                            @if ($sudahSelesai)
                                Rp {{ number_format((int) $pengembalian->denda, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="border border-[#1F2937] px-4 py-6 text-center text-[#6B7280]">
                            Tidak ada data transaksi peminjaman pada periode yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-[#F7F7F8] font-bold">
                    <td colspan="8" class="border border-[#1F2937] px-2 py-2 text-right uppercase tracking-wide text-[10px]">
                        Total Denda Terkumpul
                    </td>
                    <td class="border border-[#1F2937] px-2 py-2 text-right whitespace-nowrap">
                        Rp {{ number_format($summary['total_denda'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- Area Tanda Tangan Petugas Lab di Kanan Bawah --}}
        <div class="mt-10 flex justify-end break-inside-avoid">
            <div class="w-60 text-center text-xs">
                <p>{{ $tanggalCetakLabel }}</p>
                <p class="font-semibold text-[#1F2937] mt-0.5">Petugas Laboratorium</p>
                <div class="h-16"></div>
                <p class="font-bold text-[#1F2937] border-t border-[#1F2937] pt-1">
                    {{ auth()->user()->username }}
                </p>
            </div>
        </div>
    </main>
</body>
</html>
