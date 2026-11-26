<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Services\AlatIllustrationService;
use App\Services\LogAktivitasService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = Kategori::withCount('alat')->orderBy('id_kategori');

        if ($q !== '') {
            $lowerQ = mb_strtolower($q);
            $query->whereRaw('LOWER(nama_kategori) LIKE ?', ["%{$lowerQ}%"]);
        }

        $kategoris = $query->paginate(12)->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'q'));
    }

    public function create(): View
    {
        $illustrationConfig = AlatIllustrationService::clientPayload();

        return view('admin.kategori.create', compact('illustrationConfig'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateKategoriRequest($request, null, false);

        $kategori = Kategori::create([
            'nama_kategori' => trim($validated['nama_kategori']),
        ]);

        AlatIllustrationService::saveFromRequest(
            $kategori->id_kategori,
            $validated['illustration_mode'] ?? 'auto',
            $validated['illustration_icon'] ?? null,
            $request->file('illustration_file'),
            'kategori'
        );

        LogAktivitasService::record(
            Auth::id(),
            "Menambah kategori baru {$kategori->nama_kategori}"
        );

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori {$kategori->nama_kategori} berhasil ditambahkan.");
    }

    public function edit(Kategori $kategori): View
    {
        $illustrationConfig = AlatIllustrationService::clientPayload();
        $currentOverride = AlatIllustrationService::getOverride($kategori->id_kategori, 'kategori');
        $currentResolved = AlatIllustrationService::resolve(
            '',
            $kategori->nama_kategori,
            null,
            $kategori->id_kategori
        );

        return view('admin.kategori.edit', compact(
            'kategori',
            'illustrationConfig',
            'currentOverride',
            'currentResolved'
        ));
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $existingOverride = AlatIllustrationService::getOverride($kategori->id_kategori, 'kategori');
        $hasExistingUpload = ($existingOverride['mode'] ?? '') === 'upload' && !empty($existingOverride['file']);

        $validated = $this->validateKategoriRequest($request, $kategori->id_kategori, $hasExistingUpload);

        $namaLama = $kategori->nama_kategori;
        $kategori->nama_kategori = trim($validated['nama_kategori']);
        $kategori->save();

        AlatIllustrationService::saveFromRequest(
            $kategori->id_kategori,
            $validated['illustration_mode'] ?? 'auto',
            $validated['illustration_icon'] ?? null,
            $request->file('illustration_file'),
            'kategori'
        );

        LogAktivitasService::record(
            Auth::id(),
            "Memperbarui kategori {$namaLama} menjadi {$kategori->nama_kategori}"
        );

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori {$kategori->nama_kategori} berhasil diperbarui.");
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $jumlahAlat = $kategori->alat()->count();

        if ($jumlahAlat > 0) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', "Kategori {$kategori->nama_kategori} tidak dapat dihapus karena masih digunakan oleh {$jumlahAlat} alat.");
        }

        $idKategori = $kategori->id_kategori;
        $namaKategori = $kategori->nama_kategori;

        try {
            $kategori->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.kategori.index')
                ->with('error', "Kategori {$namaKategori} tidak dapat dihapus karena masih terikat dengan data alat.");
        }

        AlatIllustrationService::clearOverride($idKategori, 'kategori');

        LogAktivitasService::record(
            Auth::id(),
            "Menghapus kategori {$namaKategori}"
        );

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori {$namaKategori} berhasil dihapus.");
    }

    /**
     * Validasi input kategori beserta opsi ilustrasi (otomatis / pilih ikon / upload gambar).
     *
     * @return array<string, mixed>
     */
    private function validateKategoriRequest(Request $request, ?int $ignoreId, bool $hasExistingUpload): array
    {
        $uniqueRule = Rule::unique('kategori', 'nama_kategori');
        if ($ignoreId !== null) {
            $uniqueRule->ignore($ignoreId, 'id_kategori');
        }

        $requireUpload = $request->input('illustration_mode') === 'upload' && !$hasExistingUpload;

        return $request->validate(array_merge([
            'nama_kategori' => ['required', 'string', 'max:100', $uniqueRule],
        ], AlatIllustrationService::validationRules($requireUpload)), array_merge([
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
            'nama_kategori.unique' => $ignoreId !== null
                ? 'Nama kategori sudah digunakan kategori lain.'
                : 'Nama kategori sudah terdaftar.',
        ], AlatIllustrationService::validationMessages()));
    }
}
