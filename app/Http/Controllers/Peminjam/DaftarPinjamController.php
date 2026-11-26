<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class DaftarPinjamController extends Controller
{
    public function index(): View
    {
        $cart = session('daftar_pinjam', []);
        $ids = array_keys($cart);

        $alatMap = empty($ids)
            ? collect()
            : Alat::with('kategori')->whereIn('id_alat', $ids)->get()->keyBy('id_alat');

        $items = [];
        $cleanedCart = [];
        $adaMelebihiStok = false;

        foreach ($cart as $idAlat => $jumlah) {
            $alat = $alatMap->get((int) $idAlat);
            if (!$alat) {
                continue;
            }

            $qty = max(1, (int) $jumlah);
            $cleanedCart[$alat->id_alat] = $qty;
            $melebihiStok = $qty > $alat->jumlah_tersedia;

            if ($melebihiStok) {
                $adaMelebihiStok = true;
            }

            $items[] = [
                'alat' => $alat,
                'jumlah_pinjam' => $qty,
                'melebihi_stok' => $melebihiStok,
            ];
        }

        if ($cleanedCart !== $cart) {
            session(['daftar_pinjam' => $cleanedCart]);
        }

        $totalJenis = count($items);
        $totalUnit = array_sum(array_column($items, 'jumlah_pinjam'));

        return view('peminjam.daftar-pinjam.index', compact(
            'items',
            'totalJenis',
            'totalUnit',
            'adaMelebihiStok'
        ));
    }

    public function tambah(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_alat' => ['required', 'integer', 'exists:alat,id_alat'],
            'jumlah' => ['nullable', 'integer', 'min:1'],
        ]);

        $alat = Alat::with('kategori')->findOrFail($validated['id_alat']);
        $jumlahTambah = (int) ($validated['jumlah'] ?? 1);

        if ($alat->jumlah_tersedia <= 0) {
            return back()->with('error', "Stok {$alat->nama_alat} sedang habis.");
        }

        $cart = session('daftar_pinjam', []);
        $jumlahSaatIni = (int) ($cart[$alat->id_alat] ?? 0);
        $jumlahBaru = $jumlahSaatIni + $jumlahTambah;

        if ($jumlahBaru > $alat->jumlah_tersedia) {
            return back()->with(
                'error',
                "Jumlah {$alat->nama_alat} melebihi stok tersedia ({$alat->jumlah_tersedia} unit). Saat ini sudah ada {$jumlahSaatIni} unit di Daftar Pinjam."
            );
        }

        $cart[$alat->id_alat] = $jumlahBaru;
        session(['daftar_pinjam' => $cart]);

        return back()->with('added_item', [
            'id_alat' => $alat->id_alat,
            'nama_alat' => $alat->nama_alat,
            'nama_kategori' => $alat->kategori?->nama_kategori ?? 'Umum',
            'jumlah_ditambah' => $jumlahTambah,
            'jumlah_di_keranjang' => $jumlahBaru,
            'total_unit_keranjang' => array_sum($cart),
            'total_jenis_keranjang' => count($cart),
        ]);
    }

    public function update(Request $request, Alat $alat): RedirectResponse
    {
        $validated = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'jumlah.required' => 'Jumlah pinjam wajib diisi.',
            'jumlah.min' => 'Jumlah pinjam minimal 1 unit.',
        ]);

        $cart = session('daftar_pinjam', []);

        if (!array_key_exists($alat->id_alat, $cart)) {
            return redirect()
                ->route('peminjam.daftar-pinjam.index')
                ->with('error', 'Alat tersebut tidak ditemukan di Daftar Pinjam.');
        }

        $jumlahBaru = (int) $validated['jumlah'];

        if ($jumlahBaru > $alat->jumlah_tersedia) {
            return redirect()
                ->route('peminjam.daftar-pinjam.index')
                ->with('error', "Jumlah {$alat->nama_alat} tidak boleh melebihi stok tersedia ({$alat->jumlah_tersedia} unit).");
        }

        $cart[$alat->id_alat] = $jumlahBaru;
        session(['daftar_pinjam' => $cart]);

        return redirect()
            ->route('peminjam.daftar-pinjam.index')
            ->with('success', "Jumlah {$alat->nama_alat} diperbarui menjadi {$jumlahBaru} unit.");
    }

    public function hapus(Alat $alat): RedirectResponse
    {
        $cart = session('daftar_pinjam', []);

        if (array_key_exists($alat->id_alat, $cart)) {
            unset($cart[$alat->id_alat]);
            session(['daftar_pinjam' => $cart]);
        }

        return redirect()
            ->route('peminjam.daftar-pinjam.index')
            ->with('success', "{$alat->nama_alat} dihapus dari Daftar Pinjam.");
    }

    public function ajukan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib dipilih.',
            'tanggal_pinjam.date' => 'Format tanggal pinjam tidak valid.',
            'tanggal_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh sebelum hari ini.',
        ]);

        $cart = session('daftar_pinjam', []);

        if (empty($cart)) {
            return redirect()
                ->route('peminjam.daftar-pinjam.index')
                ->with('error', 'Daftar Pinjam masih kosong. Pilih minimal satu alat terlebih dahulu.');
        }

        try {
            $peminjaman = DB::transaction(function () use ($cart, $validated) {
                $alatMap = Alat::whereIn('id_alat', array_keys($cart))
                    ->get()
                    ->keyBy('id_alat');

                $detailRows = [];

                foreach ($cart as $idAlat => $jumlah) {
                    $alat = $alatMap->get((int) $idAlat);
                    $qty = (int) $jumlah;

                    if (!$alat) {
                        throw new RuntimeException('Salah satu alat di Daftar Pinjam sudah tidak tersedia di database.');
                    }

                    if ($qty < 1 || $qty > $alat->jumlah_tersedia) {
                        throw new RuntimeException("Stok {$alat->nama_alat} tidak mencukupi (tersedia {$alat->jumlah_tersedia} unit).");
                    }

                    $detailRows[] = [
                        'id_alat' => $alat->id_alat,
                        'jumlah_pinjam' => $qty,
                    ];
                }

                $peminjaman = Peminjaman::create([
                    'id_user' => Auth::id(),
                    'id_petugas' => null,
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'status' => 'menunggu',
                ]);

                foreach ($detailRows as $row) {
                    DetailPeminjaman::create([
                        'id_peminjaman' => $peminjaman->id_peminjaman,
                        'id_alat' => $row['id_alat'],
                        'jumlah_pinjam' => $row['jumlah_pinjam'],
                    ]);
                }

                $jumlahJenis = count($detailRows);
                LogAktivitasService::record(
                    Auth::id(),
                    "Mengajukan peminjaman #PMJ-{$peminjaman->id_peminjaman} ({$jumlahJenis} jenis alat)"
                );

                return $peminjaman;
            });
        } catch (RuntimeException $e) {
            return redirect()
                ->route('peminjam.daftar-pinjam.index')
                ->with('error', $e->getMessage());
        }

        session()->forget('daftar_pinjam');

        return redirect()
            ->route('peminjam.peminjaman-saya.index')
            ->with('success', "Pengajuan peminjaman #PMJ-{$peminjaman->id_peminjaman} berhasil dikirim dan menunggu persetujuan petugas.");
    }
}
