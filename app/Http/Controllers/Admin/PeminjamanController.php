<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\User;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PeminjamanController extends Controller
{
    public function index(Request $request): View
    {
        $allowedStatuses = ['semua', 'menunggu', 'disetujui', 'ditolak', 'selesai'];
        $status = (string) $request->query('status', 'semua');

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'semua';
        }

        $q = trim((string) $request->query('q', ''));

        $rawCounts = Peminjaman::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'semua' => (int) $rawCounts->sum(),
            'menunggu' => (int) ($rawCounts['menunggu'] ?? 0),
            'disetujui' => (int) ($rawCounts['disetujui'] ?? 0),
            'ditolak' => (int) ($rawCounts['ditolak'] ?? 0),
            'selesai' => (int) ($rawCounts['selesai'] ?? 0),
        ];

        $query = Peminjaman::with([
            'peminjam',
            'petugas',
            'detailPeminjaman.alat',
            'pengembalian',
        ])->orderByDesc('id_peminjaman');

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

        $peminjamans = $query->paginate(12)->withQueryString();

        $peminjams = User::where('role', 'peminjam')
            ->orderBy('username')
            ->get();

        $petugass = User::whereIn('role', ['petugas', 'admin'])
            ->orderBy('role')
            ->orderBy('username')
            ->get();

        $alats = Alat::with('kategori')
            ->orderBy('nama_alat')
            ->get();

        return view('admin.peminjaman.index', compact(
            'peminjamans',
            'status',
            'q',
            'counts',
            'peminjams',
            'petugass',
            'alats'
        ));
    }

    public function create(): View
    {
        $peminjams = User::where('role', 'peminjam')->orderBy('username')->get();
        $petugass = User::whereIn('role', ['petugas', 'admin'])
            ->orderBy('role')
            ->orderBy('username')
            ->get();
        $alats = Alat::with('kategori')->orderBy('nama_alat')->get();

        return view('admin.peminjaman.create', compact('peminjams', 'petugass', 'alats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePeminjaman($request);
        $newItems = $this->aggregateItems($validated['items']);

        try {
            $peminjaman = DB::transaction(function () use ($validated, $newItems) {
                $status = $validated['status'];

                if ($status === 'disetujui') {
                    $alatIds = array_keys($newItems);
                    sort($alatIds);

                    $alatMap = Alat::whereIn('id_alat', $alatIds)
                        ->orderBy('id_alat')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id_alat');

                    foreach ($newItems as $idAlat => $qty) {
                        $alat = $alatMap->get($idAlat);
                        if (!$alat) {
                            throw new RuntimeException("Alat ID #{$idAlat} tidak ditemukan.");
                        }

                        if ($alat->jumlah_tersedia < $qty) {
                            throw new RuntimeException(
                                "Stok {$alat->nama_alat} tidak mencukupi (diminta {$qty} unit, tersedia {$alat->jumlah_tersedia} unit)."
                            );
                        }
                    }

                    foreach ($newItems as $idAlat => $qty) {
                        $alat = $alatMap->get($idAlat);
                        $alat->jumlah_tersedia -= $qty;
                        $alat->save();
                    }
                }

                $peminjaman = Peminjaman::create([
                    'id_user' => (int) $validated['id_user'],
                    'id_petugas' => !empty($validated['id_petugas']) ? (int) $validated['id_petugas'] : null,
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'status' => $status,
                ]);

                foreach ($newItems as $idAlat => $qty) {
                    DetailPeminjaman::create([
                        'id_peminjaman' => $peminjaman->id_peminjaman,
                        'id_alat' => $idAlat,
                        'jumlah_pinjam' => $qty,
                    ]);
                }

                $peminjamUser = User::find($peminjaman->id_user);
                $usernamePeminjam = $peminjamUser?->username ?? 'peminjam';

                LogAktivitasService::record(
                    Auth::id(),
                    "Admin menambah peminjaman #PMJ-{$peminjaman->id_peminjaman} untuk {$usernamePeminjam} (status: {$status})"
                );

                return $peminjaman;
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.peminjaman.index')
            ->with('success', "Peminjaman #PMJ-{$peminjaman->id_peminjaman} berhasil ditambahkan.");
    }

    public function edit(Peminjaman $peminjaman): View
    {
        $peminjaman->load(['detailPeminjaman.alat', 'peminjam', 'petugas', 'pengembalian']);

        $peminjams = User::where('role', 'peminjam')
            ->orWhere('id_user', $peminjaman->id_user)
            ->orderBy('username')
            ->get();

        $petugass = User::whereIn('role', ['petugas', 'admin'])
            ->orWhere('id_user', $peminjaman->id_petugas)
            ->orderBy('role')
            ->orderBy('username')
            ->get();

        $alats = Alat::with('kategori')->orderBy('nama_alat')->get();

        return view('admin.peminjaman.edit', compact('peminjaman', 'peminjams', 'petugass', 'alats'));
    }

    public function update(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $validated = $this->validatePeminjaman($request, $peminjaman);
        $newItems = $this->aggregateItems($validated['items']);

        try {
            DB::transaction(function () use ($peminjaman, $validated, $newItems) {
                $locked = Peminjaman::lockForUpdate()
                    ->with(['detailPeminjaman', 'peminjam'])
                    ->findOrFail($peminjaman->id_peminjaman);

                $oldStatus = $locked->status;
                $newStatus = $validated['status'];

                $oldItems = [];
                foreach ($locked->detailPeminjaman as $detail) {
                    $idAlat = (int) $detail->id_alat;
                    $oldItems[$idAlat] = ($oldItems[$idAlat] ?? 0) + (int) $detail->jumlah_pinjam;
                }

                $allAlatIds = array_values(array_unique(array_merge(array_keys($oldItems), array_keys($newItems))));
                sort($allAlatIds);

                $alatMap = Alat::whereIn('id_alat', $allAlatIds)
                    ->orderBy('id_alat')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_alat');

                // 1. Kembalikan stok sementara jika status lama menahan stok (disetujui)
                if ($oldStatus === 'disetujui') {
                    foreach ($oldItems as $idAlat => $qty) {
                        $alat = $alatMap->get($idAlat);
                        if ($alat) {
                            $alat->jumlah_tersedia += $qty;
                        }
                    }
                }

                // 2. Jika status baru disetujui, validasi stok cukup lalu kurangi stok
                if ($newStatus === 'disetujui') {
                    foreach ($newItems as $idAlat => $qty) {
                        $alat = $alatMap->get($idAlat);
                        if (!$alat) {
                            throw new RuntimeException("Alat ID #{$idAlat} tidak ditemukan.");
                        }

                        if ($alat->jumlah_tersedia < $qty) {
                            throw new RuntimeException(
                                "Stok {$alat->nama_alat} tidak mencukupi (diminta {$qty} unit, tersedia {$alat->jumlah_tersedia} unit)."
                            );
                        }
                    }

                    foreach ($newItems as $idAlat => $qty) {
                        $alat = $alatMap->get($idAlat);
                        $alat->jumlah_tersedia -= $qty;
                    }
                }

                // 3. Simpan perubahan stok alat setelah seluruh validasi lolos
                foreach ($alatMap as $alat) {
                    if ($alat->isDirty('jumlah_tersedia')) {
                        $alat->save();
                    }
                }

                // 4. Perbarui header peminjaman & rincian item
                $locked->id_user = (int) $validated['id_user'];
                $locked->id_petugas = !empty($validated['id_petugas']) ? (int) $validated['id_petugas'] : null;
                $locked->tanggal_pinjam = $validated['tanggal_pinjam'];
                $locked->status = $newStatus;
                $locked->save();

                $locked->detailPeminjaman()->delete();

                foreach ($newItems as $idAlat => $qty) {
                    DetailPeminjaman::create([
                        'id_peminjaman' => $locked->id_peminjaman,
                        'id_alat' => $idAlat,
                        'jumlah_pinjam' => $qty,
                    ]);
                }

                LogAktivitasService::record(
                    Auth::id(),
                    "Admin memperbarui peminjaman #PMJ-{$locked->id_peminjaman} (status: {$oldStatus} -> {$newStatus})"
                );
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.peminjaman.index')
            ->with('success', "Peminjaman #PMJ-{$peminjaman->id_peminjaman} berhasil diperbarui.");
    }

    public function destroy(Peminjaman $peminjaman): RedirectResponse
    {
        $idPeminjaman = $peminjaman->id_peminjaman;

        try {
            DB::transaction(function () use ($peminjaman) {
                $locked = Peminjaman::lockForUpdate()
                    ->with(['detailPeminjaman', 'pengembalian', 'peminjam'])
                    ->findOrFail($peminjaman->id_peminjaman);

                if ($locked->status === 'disetujui') {
                    $itemsToRestore = [];
                    foreach ($locked->detailPeminjaman as $detail) {
                        $idAlat = (int) $detail->id_alat;
                        $itemsToRestore[$idAlat] = ($itemsToRestore[$idAlat] ?? 0) + (int) $detail->jumlah_pinjam;
                    }

                    if (!empty($itemsToRestore)) {
                        $alatIds = array_keys($itemsToRestore);
                        sort($alatIds);

                        $alatMap = Alat::whereIn('id_alat', $alatIds)
                            ->orderBy('id_alat')
                            ->lockForUpdate()
                            ->get()
                            ->keyBy('id_alat');

                        foreach ($itemsToRestore as $idAlat => $qty) {
                            $alat = $alatMap->get($idAlat);
                            if ($alat) {
                                $alat->jumlah_tersedia += $qty;
                                $alat->save();
                            }
                        }
                    }
                }

                $usernamePeminjam = $locked->peminjam?->username ?? 'peminjam';
                $id = $locked->id_peminjaman;

                if ($locked->pengembalian) {
                    $locked->pengembalian->delete();
                }

                $locked->detailPeminjaman()->delete();
                $locked->delete();

                LogAktivitasService::record(
                    Auth::id(),
                    "Admin menghapus peminjaman #PMJ-{$id} milik {$usernamePeminjam}"
                );
            });
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.peminjaman.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.peminjaman.index')
            ->with('success', "Peminjaman #PMJ-{$idPeminjaman} berhasil dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePeminjaman(Request $request, ?Peminjaman $existing = null): array
    {
        $userRule = $existing
            ? Rule::exists('users', 'id_user')
            : Rule::exists('users', 'id_user')->where('role', 'peminjam');

        return $request->validate([
            'id_user' => ['required', 'integer', $userRule],
            'id_petugas' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id_user')->whereIn('role', ['petugas', 'admin']),
            ],
            'tanggal_pinjam' => ['required', 'date'],
            'status' => ['required', Rule::in(['menunggu', 'disetujui', 'ditolak', 'selesai'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_alat' => ['required', 'integer', 'distinct', 'exists:alat,id_alat'],
            'items.*.jumlah_pinjam' => ['required', 'integer', 'min:1', 'max:10000'],
        ], [
            'id_user.required' => 'Peminjam wajib dipilih.',
            'id_user.exists' => 'Peminjam yang dipilih tidak valid.',
            'id_petugas.exists' => 'Petugas approver harus berrole petugas atau admin.',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
            'status.required' => 'Status peminjaman wajib dipilih.',
            'status.in' => 'Status peminjaman tidak valid.',
            'items.required' => 'Minimal harus ada 1 baris alat yang dipinjam.',
            'items.min' => 'Minimal harus ada 1 baris alat yang dipinjam.',
            'items.*.id_alat.required' => 'Alat pada setiap baris wajib dipilih.',
            'items.*.id_alat.distinct' => 'Alat yang sama tidak boleh dipilih lebih dari satu baris. Gabungkan jumlahnya pada satu baris.',
            'items.*.id_alat.exists' => 'Alat yang dipilih tidak ditemukan.',
            'items.*.jumlah_pinjam.required' => 'Jumlah pinjam wajib diisi.',
            'items.*.jumlah_pinjam.integer' => 'Jumlah pinjam harus berupa bilangan bulat.',
            'items.*.jumlah_pinjam.min' => 'Jumlah pinjam minimal 1 unit.',
        ]);
    }

    /**
     * @param  array<int, array{id_alat: int|string, jumlah_pinjam: int|string}>  $items
     * @return array<int, int>
     */
    private function aggregateItems(array $items): array
    {
        $result = [];

        foreach ($items as $row) {
            $idAlat = (int) $row['id_alat'];
            $qty = (int) $row['jumlah_pinjam'];
            $result[$idAlat] = ($result[$idAlat] ?? 0) + $qty;
        }

        ksort($result);

        return $result;
    }
}
