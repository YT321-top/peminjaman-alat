<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategoriId = $request->query('kategori');
        $ketersediaan = $request->query('ketersediaan', 'semua');
        $sort = $request->query('sort', 'nama_asc');

        $kategoris = Kategori::withCount('alat')
            ->orderBy('nama_kategori')
            ->get();

        $totalSemuaAlat = Alat::count();
        $kategoriAktif = $kategoriId ? $kategoris->firstWhere('id_kategori', (int) $kategoriId) : null;

        $query = Alat::with('kategori');

        if ($kategoriAktif) {
            $query->where('id_kategori', $kategoriAktif->id_kategori);
        }

        if ($ketersediaan === 'tersedia') {
            $query->where('jumlah_tersedia', '>', 0);
        }

        if ($q !== '') {
            $query->whereRaw('LOWER(nama_alat) LIKE ?', ['%' . mb_strtolower($q) . '%']);
        }

        match ($sort) {
            'nama_desc' => $query->orderByDesc('nama_alat'),
            'stok_desc' => $query->orderByDesc('jumlah_tersedia')->orderBy('nama_alat'),
            default => $query->orderBy('nama_alat'),
        };

        $alats = $query->paginate(12)->withQueryString();
        $daftarPinjam = session('daftar_pinjam', []);

        return view('peminjam.katalog.index', compact(
            'alats',
            'kategoris',
            'totalSemuaAlat',
            'kategoriAktif',
            'q',
            'ketersediaan',
            'sort',
            'daftarPinjam'
        ));
    }

    public function show(Alat $alat): View
    {
        $alat->load('kategori');

        $alatTerkait = Alat::with('kategori')
            ->where('id_kategori', $alat->id_kategori)
            ->where('id_alat', '!=', $alat->id_alat)
            ->orderBy('nama_alat')
            ->limit(4)
            ->get();

        $daftarPinjam = session('daftar_pinjam', []);
        $qtyDiKeranjang = (int) ($daftarPinjam[$alat->id_alat] ?? 0);
        $sisaBisaDitambah = max(0, $alat->jumlah_tersedia - $qtyDiKeranjang);

        return view('peminjam.katalog.show', compact(
            'alat',
            'alatTerkait',
            'qtyDiKeranjang',
            'sisaBisaDitambah'
        ));
    }
}
