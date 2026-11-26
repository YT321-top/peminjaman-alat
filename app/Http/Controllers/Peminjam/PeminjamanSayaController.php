<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class PeminjamanSayaController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $allowedStatuses = ['semua', 'menunggu', 'disetujui', 'selesai', 'ditolak'];
        $status = $request->query('status', 'semua');

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'semua';
        }

        $rawCounts = Peminjaman::where('id_user', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'semua' => (int) $rawCounts->sum(),
            'menunggu' => (int) ($rawCounts['menunggu'] ?? 0),
            'disetujui' => (int) ($rawCounts['disetujui'] ?? 0),
            'selesai' => (int) ($rawCounts['selesai'] ?? 0),
            'ditolak' => (int) ($rawCounts['ditolak'] ?? 0),
        ];

        $query = Peminjaman::with([
            'detailPeminjaman.alat.kategori',
            'petugas',
            'pengembalian.petugas',
        ])
            ->where('id_user', $userId)
            ->orderByDesc('id_peminjaman');

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $peminjamans = $query->paginate(10)->withQueryString();

        return view('peminjam.peminjaman-saya.index', compact('peminjamans', 'status', 'counts'));
    }

    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->id_user !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        try {
            DB::transaction(function () use ($peminjaman) {
                $locked = Peminjaman::lockForUpdate()
                    ->with('pengembalian')
                    ->findOrFail($peminjaman->id_peminjaman);

                if ($locked->status !== 'disetujui') {
                    throw new RuntimeException('Hanya peminjaman berstatus disetujui yang dapat diajukan pengembaliannya.');
                }

                if ($locked->pengembalian !== null) {
                    throw new RuntimeException('Pengembalian untuk peminjaman ini sudah diajukan dan menunggu verifikasi petugas.');
                }

                Pengembalian::create([
                    'id_peminjaman' => $locked->id_peminjaman,
                    'id_petugas' => null,
                    'tanggal_kembali' => now()->toDateString(),
                    'denda' => 0,
                ]);

                LogAktivitasService::record(
                    Auth::id(),
                    "Mengajukan pengembalian alat untuk peminjaman #PMJ-{$locked->id_peminjaman}"
                );
            });
        } catch (RuntimeException $e) {
            return redirect()
                ->route('peminjam.peminjaman-saya.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('peminjam.peminjaman-saya.index')
            ->with('success', "Pengembalian #PMJ-{$peminjaman->id_peminjaman} telah diajukan. Serahkan alat ke petugas lab untuk verifikasi.");
    }
}
