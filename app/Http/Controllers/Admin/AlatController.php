<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Kategori;
use App\Services\AlatIllustrationService;
use App\Services\LogAktivitasService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlatController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategoriId = $request->query('kategori');

        $kategoris = Kategori::orderBy('nama_kategori')->get();

        $query = Alat::with('kategori')
            ->withCount('detailPeminjaman')
            ->orderBy('id_alat');

        if ($kategoriId !== null && $kategoriId !== '' && ctype_digit((string) $kategoriId)) {
            $query->where('id_kategori', (int) $kategoriId);
        } else {
            $kategoriId = '';
        }

        if ($q !== '') {
            $lowerQ = mb_strtolower($q);
            $query->whereRaw('LOWER(nama_alat) LIKE ?', ["%{$lowerQ}%"]);
        }

        $alats = $query->paginate(12)->withQueryString();

        return view('admin.alat.index', compact('alats', 'kategoris', 'q', 'kategoriId'));
    }

    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $illustrationConfig = AlatIllustrationService::clientPayload();

        return view('admin.alat.create', compact('kategoris', 'illustrationConfig'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAlatRequest($request, false);

        $alat = Alat::create([
            'nama_alat' => trim($validated['nama_alat']),
            'id_kategori' => (int) $validated['id_kategori'],
            'jumlah_tersedia' => (int) $validated['jumlah_tersedia'],
        ]);

        AlatIllustrationService::saveFromRequest(
            $alat->id_alat,
            $validated['illustration_mode'] ?? 'auto',
            $validated['illustration_icon'] ?? null,
            $request->file('illustration_file')
        );

        LogAktivitasService::record(
            Auth::id(),
            "Menambah alat baru {$alat->nama_alat} (stok: {$alat->jumlah_tersedia} unit)"
        );

        return redirect()
            ->route('admin.alat.index')
            ->with('success', "Alat {$alat->nama_alat} berhasil ditambahkan.");
    }

    public function edit(Alat $alat): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $illustrationConfig = AlatIllustrationService::clientPayload();
        $currentOverride = AlatIllustrationService::getOverride($alat->id_alat);
        $currentResolved = AlatIllustrationService::resolve(
            $alat->nama_alat,
            $alat->kategori?->nama_kategori ?? '',
            $alat->id_alat
        );

        return view('admin.alat.edit', compact(
            'alat',
            'kategoris',
            'illustrationConfig',
            'currentOverride',
            'currentResolved'
        ));
    }

    public function update(Request $request, Alat $alat): RedirectResponse
    {
        $existingOverride = AlatIllustrationService::getOverride($alat->id_alat);
        $hasExistingUpload = ($existingOverride['mode'] ?? '') === 'upload' && !empty($existingOverride['file']);

        $validated = $this->validateAlatRequest($request, $hasExistingUpload);

        $alat->nama_alat = trim($validated['nama_alat']);
        $alat->id_kategori = (int) $validated['id_kategori'];
        $alat->jumlah_tersedia = (int) $validated['jumlah_tersedia'];
        $alat->save();

        AlatIllustrationService::saveFromRequest(
            $alat->id_alat,
            $validated['illustration_mode'] ?? 'auto',
            $validated['illustration_icon'] ?? null,
            $request->file('illustration_file')
        );

        LogAktivitasService::record(
            Auth::id(),
            "Memperbarui alat {$alat->nama_alat} (stok: {$alat->jumlah_tersedia} unit)"
        );

        return redirect()
            ->route('admin.alat.index')
            ->with('success', "Data alat {$alat->nama_alat} berhasil diperbarui.");
    }

    public function destroy(Alat $alat): RedirectResponse
    {
        if ($alat->detailPeminjaman()->exists()) {
            return redirect()
                ->route('admin.alat.index')
                ->with('error', "Alat {$alat->nama_alat} tidak dapat dihapus karena masih tercatat dalam riwayat peminjaman.");
        }

        $idAlat = $alat->id_alat;
        $namaAlat = $alat->nama_alat;

        try {
            $alat->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.alat.index')
                ->with('error', "Alat {$namaAlat} tidak dapat dihapus karena masih terikat dengan transaksi peminjaman.");
        }

        AlatIllustrationService::clearOverride($idAlat);

        LogAktivitasService::record(
            Auth::id(),
            "Menghapus alat {$namaAlat}"
        );

        return redirect()
            ->route('admin.alat.index')
            ->with('success', "Alat {$namaAlat} berhasil dihapus.");
    }

    /**
     * Validasi input form alat beserta opsi ilustrasi (otomatis / pilih ikon / upload gambar).
     *
     * @return array<string, mixed>
     */
    private function validateAlatRequest(Request $request, bool $hasExistingUpload): array
    {
        $requireUpload = $request->input('illustration_mode') === 'upload' && !$hasExistingUpload;

        return $request->validate(array_merge([
            'nama_alat' => ['required', 'string', 'max:150'],
            'id_kategori' => ['required', 'integer', 'exists:kategori,id_kategori'],
            'jumlah_tersedia' => ['required', 'integer', 'min:0', 'max:100000'],
        ], AlatIllustrationService::validationRules($requireUpload)), array_merge([
            'nama_alat.required' => 'Nama alat wajib diisi.',
            'nama_alat.max' => 'Nama alat maksimal 150 karakter.',
            'id_kategori.required' => 'Kategori alat wajib dipilih.',
            'id_kategori.exists' => 'Kategori yang dipilih tidak valid.',
            'jumlah_tersedia.required' => 'Jumlah stok tersedia wajib diisi.',
            'jumlah_tersedia.integer' => 'Jumlah stok harus berupa bilangan bulat.',
            'jumlah_tersedia.min' => 'Jumlah stok minimal 0.',
        ], AlatIllustrationService::validationMessages()));
    }
}
