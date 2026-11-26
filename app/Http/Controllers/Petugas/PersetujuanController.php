<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class PersetujuanController extends Controller
{
    public function index(Request $request): View
    {
        $allowedStatuses = ['menunggu', 'disetujui', 'ditolak', 'semua'];
        $status = (string) $request->query('status', 'menunggu');

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'menunggu';
        }

        $q = trim((string) $request->query('q', ''));

        $rawCounts = Peminjaman::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'menunggu' => (int) ($rawCounts['menunggu'] ?? 0),
            'disetujui' => (int) ($rawCounts['disetujui'] ?? 0),
            'ditolak' => (int) ($rawCounts['ditolak'] ?? 0),
            'semua' => (int) $rawCounts->sum(),
        ];

        $query = Peminjaman::with([
            'peminjam',
            'petugas',
            'detailPeminjaman.alat.kategori',
        ]);

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $lowerQ = mb_strtolower($q);
            $extractedId = null;

            if (preg_match('/^(?:#?pmj-?|#)?(\d+)$/i', $q, $matches)) {
                $extractedId = (int) $matches[1];
            }

            $query->where(function ($sub) use ($lowerQ, $extractedId) {
                $sub->whereHas('peminjam', function ($userQuery) use ($lowerQ) {
                    $userQuery->whereRaw('LOWER(username) LIKE ?', ["%{$lowerQ}%"]);
                });

                if ($extractedId !== null) {
                    $sub->orWhere('id_peminjaman', $extractedId);
                }
            });
        }

        if ($status === 'menunggu') {
            $query->orderBy('tanggal_pinjam')->orderBy('id_peminjaman');
        } else {
            $query->orderByDesc('id_peminjaman');
        }

        $peminjamans = $query->paginate(12)->withQueryString();

        return view('petugas.persetujuan.index', compact(
            'peminjamans',
            'status',
            'q',
            'counts'
        ));
    }

    public function setujui(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $locked = Peminjaman::lockForUpdate()
                    ->with(['peminjam', 'detailPeminjaman'])
                    ->findOrFail($peminjaman->id_peminjaman);

                if ($locked->status !== 'menunggu') {
                    throw new RuntimeException(
                        "Peminjaman #PMJ-{$locked->id_peminjaman} sudah tidak berstatus menunggu."
                    );
                }

                if ($locked->detailPeminjaman->isEmpty()) {
                    throw new RuntimeException(
                        "Peminjaman #PMJ-{$locked->id_peminjaman} tidak memiliki rincian alat."
                    );
                }

                $kebutuhanPerAlat = [];
                foreach ($locked->detailPeminjaman as $detail) {
                    $idAlat = (int) $detail->id_alat;
                    $kebutuhanPerAlat[$idAlat] = ($kebutuhanPerAlat[$idAlat] ?? 0) + (int) $detail->jumlah_pinjam;
                }

                ksort($kebutuhanPerAlat);
                $alatIds = array_keys($kebutuhanPerAlat);

                $alatMap = Alat::whereIn('id_alat', $alatIds)
                    ->orderBy('id_alat')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_alat');

                foreach ($kebutuhanPerAlat as $idAlat => $jumlahPinjam) {
                    $alat = $alatMap->get($idAlat);

                    if (!$alat) {
                        throw new RuntimeException(
                            "Alat dengan ID #{$idAlat} pada peminjaman #PMJ-{$locked->id_peminjaman} tidak ditemukan."
                        );
                    }

                    if ($alat->jumlah_tersedia < $jumlahPinjam) {
                        throw new RuntimeException(
                            "Stok {$alat->nama_alat} tidak mencukupi (diminta {$jumlahPinjam} unit, tersedia {$alat->jumlah_tersedia} unit). Persetujuan dibatalkan."
                        );
                    }
                }

                foreach ($kebutuhanPerAlat as $idAlat => $jumlahPinjam) {
                    $alat = $alatMap->get($idAlat);
                    $alat->jumlah_tersedia -= $jumlahPinjam;
                    $alat->save();
                }

                $locked->status = 'disetujui';
                $locked->id_petugas = Auth::id();
                $locked->save();

                $usernamePeminjam = $locked->peminjam?->username ?? 'peminjam';
                LogAktivitasService::record(
                    Auth::id(),
                    "Menyetujui peminjaman #PMJ-{$locked->id_peminjaman} milik {$usernamePeminjam}"
                );
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Peminjaman #PMJ-{$peminjaman->id_peminjaman} berhasil disetujui dan stok alat telah dikurangi."
        );
    }

    public function tolak(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                $locked = Peminjaman::lockForUpdate()
                    ->with('peminjam')
                    ->findOrFail($peminjaman->id_peminjaman);

                if ($locked->status !== 'menunggu') {
                    throw new RuntimeException(
                        "Peminjaman #PMJ-{$locked->id_peminjaman} sudah tidak berstatus menunggu."
                    );
                }

                $locked->status = 'ditolak';
                $locked->id_petugas = Auth::id();
                $locked->save();

                $usernamePeminjam = $locked->peminjam?->username ?? 'peminjam';
                LogAktivitasService::record(
                    Auth::id(),
                    "Menolak peminjaman #PMJ-{$locked->id_peminjaman} milik {$usernamePeminjam}"
                );
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Peminjaman #PMJ-{$peminjaman->id_peminjaman} telah ditolak."
        );
    }
}
