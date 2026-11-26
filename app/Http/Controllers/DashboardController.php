<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $stats = [
                'total_user' => User::count(),
                'total_alat' => Alat::count(),
                'sedang_dipinjam' => Peminjaman::where('status', 'disetujui')->count(),
                'menunggu_persetujuan' => Peminjaman::where('status', 'menunggu')->count(),
            ];

            $logTerbaru = LogAktivitas::with('user')
                ->orderByDesc('waktu')
                ->orderByDesc('id_log')
                ->limit(8)
                ->get();

            $alatStokMenipis = Alat::with('kategori')
                ->where('jumlah_tersedia', '<=', 3)
                ->orderBy('jumlah_tersedia')
                ->orderBy('nama_alat')
                ->limit(6)
                ->get();

            return view('dashboard.admin', compact('stats', 'logTerbaru', 'alatStokMenipis'));
        }

        if ($user->role === 'petugas') {
            $stats = [
                'antrean_menunggu' => Peminjaman::where('status', 'menunggu')->count(),
                'sedang_dipinjam' => Peminjaman::where('status', 'disetujui')->count(),
                'menunggu_verifikasi' => Peminjaman::where('status', 'disetujui')
                    ->whereHas('pengembalian', fn ($q) => $q->whereNull('id_petugas'))
                    ->count(),
                'total_denda_bulan_ini' => (int) Pengembalian::whereNotNull('id_petugas')
                    ->whereDate('tanggal_kembali', '>=', now()->startOfMonth()->toDateString())
                    ->whereDate('tanggal_kembali', '<=', now()->endOfMonth()->toDateString())
                    ->sum('denda'),
            ];

            $antreanMenunggu = Peminjaman::with(['peminjam', 'detailPeminjaman.alat'])
                ->where('status', 'menunggu')
                ->orderBy('tanggal_pinjam')
                ->orderBy('id_peminjaman')
                ->limit(8)
                ->get();

            $menungguVerifikasiKembali = Peminjaman::with(['peminjam', 'detailPeminjaman.alat', 'pengembalian'])
                ->where('status', 'disetujui')
                ->whereHas('pengembalian', fn ($q) => $q->whereNull('id_petugas'))
                ->orderBy('tanggal_pinjam')
                ->orderBy('id_peminjaman')
                ->limit(5)
                ->get();

            return view('dashboard.petugas', compact('stats', 'antreanMenunggu', 'menungguVerifikasiKembali'));
        }

        return $this->peminjam();
    }

    public function peminjam(): View
    {
        $user = Auth::user();

        $rawCounts = Peminjaman::where('id_user', $user->id_user)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $ringkasan = [
            'sedang_dipinjam' => (int) ($rawCounts['disetujui'] ?? 0),
            'menunggu' => (int) ($rawCounts['menunggu'] ?? 0),
            'selesai' => (int) ($rawCounts['selesai'] ?? 0),
            'ditolak' => (int) ($rawCounts['ditolak'] ?? 0),
            'total_riwayat' => (int) $rawCounts->sum(),
        ];

        $peminjamanAktif = Peminjaman::with('detailPeminjaman.alat')
            ->where('id_user', $user->id_user)
            ->where('status', 'disetujui')
            ->orderBy('tanggal_pinjam')
            ->orderBy('id_peminjaman')
            ->get();

        $namaAlatAktif = $peminjamanAktif
            ->flatMap(fn ($p) => $p->detailPeminjaman->map(fn ($d) => $d->alat?->nama_alat))
            ->filter()
            ->unique()
            ->take(2)
            ->values();

        $ringkasanAktifSubtext = $namaAlatAktif->isNotEmpty()
            ? $namaAlatAktif->implode(', ')
            : 'Belum ada alat yang sedang dipinjam';

        $alatPopuler = Alat::with('kategori')
            ->withCount('detailPeminjaman')
            ->withSum('detailPeminjaman', 'jumlah_pinjam')
            ->orderByDesc('detail_peminjaman_count')
            ->orderByDesc('jumlah_tersedia')
            ->orderBy('nama_alat')
            ->limit(5)
            ->get();

        $totalAlat = Alat::count();
        $totalKategori = Kategori::count();

        $kategoriUnggulan = Kategori::with(['alat' => fn ($q) => $q->orderByDesc('jumlah_tersedia')->limit(3)])
            ->withCount('alat')
            ->orderByDesc('alat_count')
            ->orderBy('nama_kategori')
            ->first();

        $daftarPinjam = session('daftar_pinjam', []);
        $jumlahDaftarPinjam = is_array($daftarPinjam) ? array_sum(array_map('intval', $daftarPinjam)) : 0;

        return view('dashboard.peminjam', compact(
            'ringkasan',
            'ringkasanAktifSubtext',
            'alatPopuler',
            'totalAlat',
            'totalKategori',
            'kategoriUnggulan',
            'daftarPinjam',
            'jumlahDaftarPinjam'
        ));
    }
}
