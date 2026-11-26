<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Services\DendaService;
use App\Services\LogAktivitasService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class PengembalianController extends Controller
{
    public function index(Request $request): View
    {
        $allowedTabs = ['verifikasi', 'dipinjam', 'selesai'];
        $tab = (string) $request->query('tab', 'verifikasi');

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'verifikasi';
        }

        $q = trim((string) $request->query('q', ''));

        $counts = [
            'verifikasi' => Peminjaman::where('status', 'disetujui')
                ->whereHas('pengembalian', fn ($sub) => $sub->whereNull('id_petugas'))
                ->count(),
            'dipinjam' => Peminjaman::where('status', 'disetujui')
                ->whereDoesntHave('pengembalian')
                ->count(),
            'selesai' => Peminjaman::where('status', 'selesai')->count(),
        ];

        $query = Peminjaman::with([
            'peminjam',
            'petugas',
            'detailPeminjaman.alat.kategori',
            'pengembalian.petugas',
        ]);

        if ($tab === 'verifikasi') {
            $query->where('status', 'disetujui')
                ->whereHas('pengembalian', fn ($sub) => $sub->whereNull('id_petugas'))
                ->orderBy('tanggal_pinjam')
                ->orderBy('id_peminjaman');
        } elseif ($tab === 'dipinjam') {
            $query->where('status', 'disetujui')
                ->whereDoesntHave('pengembalian')
                ->orderBy('tanggal_pinjam')
                ->orderBy('id_peminjaman');
        } else {
            $query->where('status', 'selesai')
                ->orderByDesc('id_peminjaman');
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

        $peminjamans = $query->paginate(12)->withQueryString();

        $estimasiMap = [];
        foreach ($peminjamans as $pinjam) {
            $defaultTanggalKembali = $pinjam->pengembalian?->tanggal_kembali
                ? $pinjam->pengembalian->tanggal_kembali->copy()
                : today();

            $estimasi = DendaService::hitungEstimasi($pinjam->tanggal_pinjam, $defaultTanggalKembali);

            $estimasiMap[$pinjam->id_peminjaman] = [
                'default_tanggal_kembali' => $defaultTanggalKembali->toDateString(),
                'hari_pinjam' => $estimasi['hari_pinjam'],
                'hari_terlambat' => $estimasi['hari_terlambat'],
                'estimasi_denda' => $estimasi['estimasi_denda'],
            ];
        }

        $batasHari = DendaService::BATAS_HARI_PINJAM;
        $tarifPerHari = DendaService::TARIF_DENDA_PER_HARI;

        return view('petugas.pengembalian.index', compact(
            'peminjamans',
            'tab',
            'q',
            'counts',
            'estimasiMap',
            'batasHari',
            'tarifPerHari'
        ));
    }

    public function konfirmasi(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_kembali' => ['required', 'date'],
            'denda' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ], [
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
            'denda.required' => 'Nominal denda wajib diisi (isi 0 jika tidak ada denda).',
            'denda.numeric' => 'Nominal denda harus berupa angka.',
            'denda.min' => 'Nominal denda tidak boleh kurang dari 0.',
            'denda.max' => 'Nominal denda melebihi batas maksimum.',
        ]);

        $tanggalKembali = Carbon::parse($validated['tanggal_kembali'])->startOfDay();
        $denda = (int) round((float) $validated['denda']);

        try {
            DB::transaction(function () use ($peminjaman, $tanggalKembali, $denda) {
                $locked = Peminjaman::lockForUpdate()
                    ->with(['peminjam', 'detailPeminjaman'])
                    ->findOrFail($peminjaman->id_peminjaman);

                if ($locked->status !== 'disetujui') {
                    throw new RuntimeException(
                        "Peminjaman #PMJ-{$locked->id_peminjaman} tidak dalam status disetujui sehingga tidak dapat dikonfirmasi pengembaliannya."
                    );
                }

                if ($tanggalKembali->lt($locked->tanggal_pinjam->copy()->startOfDay())) {
                    throw new RuntimeException(
                        'Tanggal kembali tidak boleh lebih awal dari tanggal pinjam (' . $locked->tanggal_pinjam->format('d/m/Y') . ').'
                    );
                }

                Pengembalian::updateOrCreate(
                    ['id_peminjaman' => $locked->id_peminjaman],
                    [
                        'id_petugas' => Auth::id(),
                        'tanggal_kembali' => $tanggalKembali->toDateString(),
                        'denda' => $denda,
                    ]
                );

                $pengembalianPerAlat = [];
                foreach ($locked->detailPeminjaman as $detail) {
                    $idAlat = (int) $detail->id_alat;
                    $pengembalianPerAlat[$idAlat] = ($pengembalianPerAlat[$idAlat] ?? 0) + (int) $detail->jumlah_pinjam;
                }

                if (!empty($pengembalianPerAlat)) {
                    ksort($pengembalianPerAlat);
                    $alatIds = array_keys($pengembalianPerAlat);

                    $alatMap = Alat::whereIn('id_alat', $alatIds)
                        ->orderBy('id_alat')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id_alat');

                    foreach ($pengembalianPerAlat as $idAlat => $jumlahKembali) {
                        $alat = $alatMap->get($idAlat);
                        if ($alat) {
                            $alat->jumlah_tersedia += $jumlahKembali;
                            $alat->save();
                        }
                    }
                }

                $locked->status = 'selesai';
                $locked->save();

                $formattedDenda = number_format($denda, 0, ',', '.');
                LogAktivitasService::record(
                    Auth::id(),
                    "Mengonfirmasi pengembalian #PMJ-{$locked->id_peminjaman} (Denda: Rp {$formattedDenda})"
                );
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $formattedDenda = number_format($denda, 0, ',', '.');

        return back()->with(
            'success',
            "Pengembalian #PMJ-{$peminjaman->id_peminjaman} berhasil dikonfirmasi (Denda: Rp {$formattedDenda}). Stok alat telah dikembalikan."
        );
    }
}
