<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LaporanController extends Controller
{
    private const ALLOWED_STATUSES = ['semua', 'menunggu', 'disetujui', 'ditolak', 'selesai'];

    private const BULAN_ID = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        $allRows = $this->buildQuery($filters)->get();
        $summary = $this->computeSummary($allRows);

        $peminjamans = $this->buildQuery($filters)
            ->paginate(15)
            ->withQueryString();

        return view('petugas.laporan.index', [
            'peminjamans' => $peminjamans,
            'summary' => $summary,
            'tanggalMulai' => $filters['tanggal_mulai'],
            'tanggalSelesai' => $filters['tanggal_selesai'],
            'status' => $filters['status'],
        ]);
    }

    public function cetak(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $peminjamans = $this->buildQuery($filters)->get();
        $summary = $this->computeSummary($peminjamans);

        $mulaiCarbon = Carbon::createFromFormat('Y-m-d', $filters['tanggal_mulai']);
        $selesaiCarbon = Carbon::createFromFormat('Y-m-d', $filters['tanggal_selesai']);
        $now = now();

        $periodeLabel = $this->formatTanggalIndonesia($mulaiCarbon) . ' s/d ' . $this->formatTanggalIndonesia($selesaiCarbon);
        $tanggalCetakLabel = $this->formatTanggalIndonesia($now);
        $waktuCetakLabel = $now->format('d/m/Y H:i') . ' WIB';
        $statusLabel = $this->statusLabel($filters['status']);

        return view('petugas.laporan.cetak', [
            'peminjamans' => $peminjamans,
            'summary' => $summary,
            'tanggalMulai' => $filters['tanggal_mulai'],
            'tanggalSelesai' => $filters['tanggal_selesai'],
            'status' => $filters['status'],
            'statusLabel' => $statusLabel,
            'periodeLabel' => $periodeLabel,
            'tanggalCetakLabel' => $tanggalCetakLabel,
            'waktuCetakLabel' => $waktuCetakLabel,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $peminjamans = $this->buildQuery($filters)->get();

        $filename = sprintf(
            'laporan-peminjaman-%s-sd-%s.csv',
            $filters['tanggal_mulai'],
            $filters['tanggal_selesai']
        );

        return response()->streamDownload(function () use ($peminjamans) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Excel langsung membaca karakter UTF-8 dengan rapi
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Kode',
                'Peminjam',
                'Tanggal Pinjam',
                'Daftar Alat & Jumlah',
                'Total Unit',
                'Status',
                'Petugas Penyetuju',
                'Tanggal Kembali',
                'Petugas Penerima',
                'Denda (Rp)',
            ]);

            foreach ($peminjamans as $index => $pinjam) {
                $daftarAlat = $pinjam->detailPeminjaman
                    ->map(fn ($detail) => sprintf(
                        '%s (%d unit)',
                        $detail->alat?->nama_alat ?? 'Alat tidak ditemukan',
                        (int) $detail->jumlah_pinjam
                    ))
                    ->implode('; ');

                $totalUnit = (int) $pinjam->detailPeminjaman->sum('jumlah_pinjam');
                $pengembalian = $pinjam->pengembalian;
                $sudahDikonfirmasi = $pinjam->status === 'selesai' && $pengembalian !== null;

                fputcsv($handle, [
                    $index + 1,
                    '#PMJ-' . $pinjam->id_peminjaman,
                    $pinjam->peminjam?->username ?? '-',
                    $pinjam->tanggal_pinjam?->format('d/m/Y') ?? '-',
                    $daftarAlat !== '' ? $daftarAlat : '-',
                    $totalUnit,
                    $this->statusLabel($pinjam->status),
                    $pinjam->petugas?->username ?? '-',
                    $pengembalian?->tanggal_kembali?->format('d/m/Y') ?? '-',
                    $pengembalian?->petugas?->username ?? '-',
                    $sudahDikonfirmasi ? (int) $pengembalian->denda : 0,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{tanggal_mulai: string, tanggal_selesai: string, status: string}
     */
    private function resolveFilters(Request $request): array
    {
        $defaultMulai = now()->startOfMonth()->toDateString();
        $defaultSelesai = now()->endOfMonth()->toDateString();

        $tanggalMulai = $this->parseDateOrDefault($request->query('tanggal_mulai'), $defaultMulai);
        $tanggalSelesai = $this->parseDateOrDefault($request->query('tanggal_selesai'), $defaultSelesai);

        if ($tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        $status = (string) $request->query('status', 'semua');
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $status = 'semua';
        }

        return [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status' => $status,
        ];
    }

    private function parseDateOrDefault(mixed $value, string $fallback): string
    {
        if (!is_string($value) || trim($value) === '') {
            return $fallback;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', trim($value));
            if ($parsed !== false && $parsed->format('Y-m-d') === trim($value)) {
                return $parsed->toDateString();
            }
        } catch (Throwable) {
            // Gunakan fallback jika format tanggal tidak valid
        }

        return $fallback;
    }

    private function buildQuery(array $filters): Builder
    {
        $query = Peminjaman::with([
            'peminjam',
            'petugas',
            'detailPeminjaman.alat',
            'pengembalian.petugas',
        ])
            ->whereDate('tanggal_pinjam', '>=', $filters['tanggal_mulai'])
            ->whereDate('tanggal_pinjam', '<=', $filters['tanggal_selesai']);

        if ($filters['status'] !== 'semua') {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('tanggal_pinjam')
            ->orderBy('id_peminjaman');
    }

    /**
     * @return array{total_transaksi: int, total_alat_dipinjam: int, transaksi_selesai: int, total_denda: int}
     */
    private function computeSummary(Collection $rows): array
    {
        $totalAlatDipinjam = 0;
        $transaksiSelesai = 0;
        $totalDenda = 0;

        foreach ($rows as $row) {
            $totalAlatDipinjam += (int) $row->detailPeminjaman->sum('jumlah_pinjam');

            if ($row->status === 'selesai') {
                $transaksiSelesai++;
                $totalDenda += (int) ($row->pengembalian?->denda ?? 0);
            }
        }

        return [
            'total_transaksi' => $rows->count(),
            'total_alat_dipinjam' => $totalAlatDipinjam,
            'transaksi_selesai' => $transaksiSelesai,
            'total_denda' => $totalDenda,
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
            default => 'Semua Status',
        };
    }

    private function formatTanggalIndonesia(Carbon $date): string
    {
        $bulan = self::BULAN_ID[(int) $date->format('n')] ?? $date->format('F');

        return $date->format('d') . ' ' . $bulan . ' ' . $date->format('Y');
    }
}
