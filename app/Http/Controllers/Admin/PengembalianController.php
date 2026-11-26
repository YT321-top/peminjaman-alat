<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use App\Services\DendaService;
use App\Services\LogAktivitasService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PengembalianController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $tanggalMulai = $this->parseOptionalDate($request->query('tanggal_mulai'));
        $tanggalSelesai = $this->parseOptionalDate($request->query('tanggal_selesai'));

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        $query = Pengembalian::with([
            'peminjaman.peminjam',
            'peminjaman.detailPeminjaman.alat',
            'petugas',
        ])->orderByDesc('id_pengembalian');

        if ($tanggalMulai) {
            $query->whereDate('tanggal_kembali', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_kembali', '<=', $tanggalSelesai);
        }

        if ($q !== '') {
            $lowerQ = mb_strtolower($q);
            $extractedId = null;

            if (preg_match('/^(?:#?pmj-?|#)?(\d+)$/i', $q, $matches)) {
                $extractedId = (int) $matches[1];
            }

            $query->where(function ($sub) use ($lowerQ, $extractedId) {
                $sub->whereHas('peminjaman.peminjam', function ($userQuery) use ($lowerQ) {
                    $userQuery->whereRaw('LOWER(username) LIKE ?', ["%{$lowerQ}%"]);
                });

                if ($extractedId !== null) {
                    $sub->orWhere('id_peminjaman', $extractedId);
                }
            });
        }

        $pengembalians = $query->paginate(12)->withQueryString();

        return view('admin.pengembalian.index', compact(
            'pengembalians',
            'q',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    public function create(): View
    {
        $peminjamanAktif = Peminjaman::with(['peminjam', 'detailPeminjaman.alat'])
            ->where('status', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->orderBy('tanggal_pinjam')
            ->orderBy('id_peminjaman')
            ->get();

        $petugass = User::whereIn('role', ['petugas', 'admin'])
            ->orderBy('role')
            ->orderBy('username')
            ->get();

        $peminjamanMeta = [];
        foreach ($peminjamanAktif as $pinjam) {
            $peminjamanMeta[$pinjam->id_peminjaman] = [
                'kode' => '#PMJ-' . $pinjam->id_peminjaman,
                'peminjam' => $pinjam->peminjam?->username ?? '-',
                'tanggal_pinjam' => $pinjam->tanggal_pinjam?->toDateString(),
                'tanggal_pinjam_label' => $pinjam->tanggal_pinjam?->format('d/m/Y') ?? '-',
                'items' => $pinjam->detailPeminjaman->map(fn ($d) => [
                    'nama' => $d->alat?->nama_alat ?? 'Alat tidak ditemukan',
                    'jumlah' => (int) $d->jumlah_pinjam,
                ])->values()->all(),
            ];
        }

        $batasHari = DendaService::BATAS_HARI_PINJAM;
        $tarifPerHari = DendaService::TARIF_DENDA_PER_HARI;

        return view('admin.pengembalian.create', compact(
            'peminjamanAktif',
            'petugass',
            'peminjamanMeta',
            'batasHari',
            'tarifPerHari'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_peminjaman' => ['required', 'integer', 'exists:peminjaman,id_peminjaman'],
            'id_petugas' => [
                'required',
                'integer',
                Rule::exists('users', 'id_user')->whereIn('role', ['petugas', 'admin']),
            ],
            'tanggal_kembali' => ['required', 'date'],
            'denda' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ], [
            'id_peminjaman.required' => 'Transaksi peminjaman wajib dipilih.',
            'id_peminjaman.exists' => 'Transaksi peminjaman tidak ditemukan.',
            'id_petugas.required' => 'Petugas penerima wajib dipilih.',
            'id_petugas.exists' => 'Petugas penerima tidak valid.',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
            'denda.required' => 'Nominal denda wajib diisi (isi 0 jika tidak ada denda).',
            'denda.numeric' => 'Nominal denda harus berupa angka.',
            'denda.min' => 'Nominal denda minimal 0.',
        ]);

        $tanggalKembali = Carbon::parse($validated['tanggal_kembali'])->startOfDay();
        $denda = (int) round((float) $validated['denda']);

        try {
            $idPeminjaman = DB::transaction(function () use ($validated, $tanggalKembali, $denda) {
                $locked = Peminjaman::lockForUpdate()
                    ->with(['detailPeminjaman', 'pengembalian'])
                    ->findOrFail((int) $validated['id_peminjaman']);

                if ($locked->status !== 'disetujui') {
                    throw new RuntimeException(
                        "Peminjaman #PMJ-{$locked->id_peminjaman} bukan berstatus disetujui."
                    );
                }

                if ($locked->pengembalian !== null) {
                    throw new RuntimeException(
                        "Peminjaman #PMJ-{$locked->id_peminjaman} sudah memiliki data pengembalian."
                    );
                }

                if ($tanggalKembali->lt($locked->tanggal_pinjam->copy()->startOfDay())) {
                    throw new RuntimeException(
                        'Tanggal kembali tidak boleh lebih awal dari tanggal pinjam (' . $locked->tanggal_pinjam->format('d/m/Y') . ').'
                    );
                }

                Pengembalian::create([
                    'id_peminjaman' => $locked->id_peminjaman,
                    'id_petugas' => (int) $validated['id_petugas'],
                    'tanggal_kembali' => $tanggalKembali->toDateString(),
                    'denda' => $denda,
                ]);

                $this->restoreAlatStock($locked);

                $locked->status = 'selesai';
                $locked->save();

                $formattedDenda = number_format($denda, 0, ',', '.');
                LogAktivitasService::record(
                    Auth::id(),
                    "Admin mencatat pengembalian #PMJ-{$locked->id_peminjaman} (Denda: Rp {$formattedDenda})"
                );

                return $locked->id_peminjaman;
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pengembalian.index')
            ->with('success', "Pengembalian #PMJ-{$idPeminjaman} berhasil dicatat dan stok alat telah dikembalikan.");
    }

    public function edit(Pengembalian $pengembalian): View
    {
        $pengembalian->load(['peminjaman.peminjam', 'peminjaman.detailPeminjaman.alat', 'petugas']);

        $petugass = User::whereIn('role', ['petugas', 'admin'])
            ->orderBy('role')
            ->orderBy('username')
            ->get();

        $batasHari = DendaService::BATAS_HARI_PINJAM;
        $tarifPerHari = DendaService::TARIF_DENDA_PER_HARI;

        return view('admin.pengembalian.edit', compact(
            'pengembalian',
            'petugass',
            'batasHari',
            'tarifPerHari'
        ));
    }

    public function update(Request $request, Pengembalian $pengembalian): RedirectResponse
    {
        $validated = $request->validate([
            'id_petugas' => [
                'required',
                'integer',
                Rule::exists('users', 'id_user')->whereIn('role', ['petugas', 'admin']),
            ],
            'tanggal_kembali' => ['required', 'date'],
            'denda' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ], [
            'id_petugas.required' => 'Petugas penerima wajib dipilih.',
            'id_petugas.exists' => 'Petugas penerima tidak valid.',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.date' => 'Format tanggal kembali tidak valid.',
            'denda.required' => 'Nominal denda wajib diisi.',
            'denda.numeric' => 'Nominal denda harus berupa angka.',
            'denda.min' => 'Nominal denda minimal 0.',
        ]);

        $tanggalKembali = Carbon::parse($validated['tanggal_kembali'])->startOfDay();
        $denda = (int) round((float) $validated['denda']);

        try {
            DB::transaction(function () use ($pengembalian, $validated, $tanggalKembali, $denda) {
                $lockedPengembalian = Pengembalian::lockForUpdate()
                    ->findOrFail($pengembalian->id_pengembalian);

                $lockedPinjam = Peminjaman::lockForUpdate()
                    ->with('detailPeminjaman')
                    ->findOrFail($lockedPengembalian->id_peminjaman);

                if ($tanggalKembali->lt($lockedPinjam->tanggal_pinjam->copy()->startOfDay())) {
                    throw new RuntimeException(
                        'Tanggal kembali tidak boleh lebih awal dari tanggal pinjam (' . $lockedPinjam->tanggal_pinjam->format('d/m/Y') . ').'
                    );
                }

                $lockedPengembalian->id_petugas = (int) $validated['id_petugas'];
                $lockedPengembalian->tanggal_kembali = $tanggalKembali->toDateString();
                $lockedPengembalian->denda = $denda;
                $lockedPengembalian->save();

                // Jika sebelumnya pengajuan kembali dari peminjam yang belum diverifikasi (masih disetujui),
                // selesaikan transaksi dan kembalikan stok agar konsisten.
                if ($lockedPinjam->status === 'disetujui') {
                    $this->restoreAlatStock($lockedPinjam);
                    $lockedPinjam->status = 'selesai';
                    $lockedPinjam->save();
                }

                $formattedDenda = number_format($denda, 0, ',', '.');
                LogAktivitasService::record(
                    Auth::id(),
                    "Admin memperbarui pengembalian #PMJ-{$lockedPinjam->id_peminjaman} (Denda: Rp {$formattedDenda})"
                );
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pengembalian.index')
            ->with('success', "Data pengembalian #PMJ-{$pengembalian->id_peminjaman} berhasil diperbarui.");
    }

    public function destroy(Pengembalian $pengembalian): RedirectResponse
    {
        $idPeminjaman = $pengembalian->id_peminjaman;

        try {
            DB::transaction(function () use ($pengembalian) {
                $lockedPengembalian = Pengembalian::lockForUpdate()
                    ->findOrFail($pengembalian->id_pengembalian);

                $lockedPinjam = Peminjaman::lockForUpdate()
                    ->with('detailPeminjaman')
                    ->findOrFail($lockedPengembalian->id_peminjaman);

                if ($lockedPinjam->status === 'selesai') {
                    $itemsToDeduct = [];
                    foreach ($lockedPinjam->detailPeminjaman as $detail) {
                        $idAlat = (int) $detail->id_alat;
                        $itemsToDeduct[$idAlat] = ($itemsToDeduct[$idAlat] ?? 0) + (int) $detail->jumlah_pinjam;
                    }

                    if (!empty($itemsToDeduct)) {
                        $alatIds = array_keys($itemsToDeduct);
                        sort($alatIds);

                        $alatMap = Alat::whereIn('id_alat', $alatIds)
                            ->orderBy('id_alat')
                            ->lockForUpdate()
                            ->get()
                            ->keyBy('id_alat');

                        foreach ($itemsToDeduct as $idAlat => $qty) {
                            $alat = $alatMap->get($idAlat);
                            if (!$alat) {
                                throw new RuntimeException("Alat ID #{$idAlat} tidak ditemukan.");
                            }

                            if ($alat->jumlah_tersedia < $qty) {
                                throw new RuntimeException(
                                    "Pengembalian #PMJ-{$lockedPinjam->id_peminjaman} tidak dapat dihapus karena stok {$alat->nama_alat} tidak mencukupi untuk mengembalikan status peminjaman ke disetujui (butuh {$qty} unit, tersedia {$alat->jumlah_tersedia} unit)."
                                );
                            }
                        }

                        foreach ($itemsToDeduct as $idAlat => $qty) {
                            $alat = $alatMap->get($idAlat);
                            $alat->jumlah_tersedia -= $qty;
                            $alat->save();
                        }
                    }

                    $lockedPinjam->status = 'disetujui';
                    $lockedPinjam->save();
                }

                $lockedPengembalian->delete();

                LogAktivitasService::record(
                    Auth::id(),
                    "Admin menghapus data pengembalian #PMJ-{$lockedPinjam->id_peminjaman}"
                );
            });
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.pengembalian.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pengembalian.index')
            ->with('success', "Data pengembalian #PMJ-{$idPeminjaman} berhasil dihapus dan status peminjaman dikembalikan ke disetujui.");
    }

    private function restoreAlatStock(Peminjaman $peminjaman): void
    {
        $itemsToRestore = [];
        foreach ($peminjaman->detailPeminjaman as $detail) {
            $idAlat = (int) $detail->id_alat;
            $itemsToRestore[$idAlat] = ($itemsToRestore[$idAlat] ?? 0) + (int) $detail->jumlah_pinjam;
        }

        if (empty($itemsToRestore)) {
            return;
        }

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

    private function parseOptionalDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', trim($value));
            if ($parsed !== false && $parsed->format('Y-m-d') === trim($value)) {
                return $parsed->toDateString();
            }
        } catch (Throwable) {
            // Abaikan tanggal yang formatnya tidak valid
        }

        return null;
    }
}
