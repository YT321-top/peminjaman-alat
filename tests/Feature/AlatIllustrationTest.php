<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Services\AlatIllustrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AlatIllustrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        foreach (['alat-illustrations.json', 'kategori-illustrations.json'] as $manifestFile) {
            $path = storage_path('framework/testing/' . $manifestFile);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        foreach (['uploads/alat-testing', 'uploads/kategori-testing'] as $uploadDir) {
            $dir = public_path($uploadDir);
            if (File::isDirectory($dir)) {
                File::deleteDirectory($dir);
            }
        }

        AlatIllustrationService::resetCache();

        parent::tearDown();
    }

    private function buatAdmin(): User
    {
        return User::create([
            'username' => 'adm_illu_' . uniqid(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    private function buatKategori(string $nama = 'Lab Biologi'): Kategori
    {
        return Kategori::create([
            'nama_kategori' => $nama . ' ' . uniqid(),
        ]);
    }

    public function test_pemetaan_otomatis_mendukung_alat_baru_word_boundary_dan_fallback_netral(): void
    {
        $penggaris = AlatIllustrationService::resolve('Penggaris Besi 30cm', 'Lab Fisika');
        $this->assertSame('penggaris', $penggaris['type']);
        $this->assertSame('matched', $penggaris['status']);

        $termometer = AlatIllustrationService::resolve('Termometer Alkohol', 'Lab Kimia');
        $this->assertSame('termometer', $termometer['type']);
        $this->assertSame('matched', $termometer['status']);

        // Kata mengandung 'lan' (bantalan) tidak boleh nyasar ke ikon LAN/jaringan
        $bantalan = AlatIllustrationService::resolve('Bantalan Karet', 'Lab Komputer & Jaringan');
        $this->assertSame('default', $bantalan['type']);
        $this->assertSame('unmatched', $bantalan['status']);

        // Input bebas/non-lab tetap memakai fallback netral meskipun kategori Lab Biologi
        foreach (['ikan', 'tai'] as $kataBebas) {
            $res = AlatIllustrationService::resolve($kataBebas, 'Lab Biologi');
            $this->assertSame('default', $res['type']);
            $this->assertSame('unmatched', $res['status']);
        }

        // Kartu kategori (namaAlat kosong) tetap memakai ikon kategori
        $katOnly = AlatIllustrationService::resolve('', 'Lab Biologi');
        $this->assertSame('mikroskop', $katOnly['type']);
        $this->assertSame('category', $katOnly['status']);
    }

    public function test_admin_dapat_simpan_alat_mode_otomatis_dan_pilih_ikon_manual(): void
    {
        $admin = $this->buatAdmin();
        $kat = $this->buatKategori('Lab Uji');

        // 1. Input bebas 'ikan' tetap bisa disimpan dengan fallback netral
        $this->actingAs($admin)
            ->post(route('admin.alat.store'), [
                'nama_alat' => 'ikan',
                'id_kategori' => $kat->id_kategori,
                'jumlah_tersedia' => 3,
                'illustration_mode' => 'auto',
            ])
            ->assertRedirect(route('admin.alat.index'));

        $ikan = Alat::where('nama_alat', 'ikan')->firstOrFail();
        $this->assertNull(AlatIllustrationService::getOverride($ikan->id_alat));

        // 2. Pilih ikon manual pada form tambah
        $this->actingAs($admin)
            ->post(route('admin.alat.store'), [
                'nama_alat' => 'Benda Khusus X',
                'id_kategori' => $kat->id_kategori,
                'jumlah_tersedia' => 4,
                'illustration_mode' => 'manual_icon',
                'illustration_icon' => 'bunsen',
            ])
            ->assertRedirect(route('admin.alat.index'));

        $alatManual = Alat::where('nama_alat', 'Benda Khusus X')->firstOrFail();
        $override = AlatIllustrationService::getOverride($alatManual->id_alat);
        $this->assertSame('manual_icon', $override['mode']);
        $this->assertSame('bunsen', $override['type']);

        $resolved = AlatIllustrationService::resolve($alatManual->nama_alat, $kat->nama_kategori, $alatManual->id_alat);
        $this->assertSame('bunsen', $resolved['type']);
    }

    public function test_admin_dapat_upload_gambar_manual_dan_dibersihkan_saat_hapus(): void
    {
        $admin = $this->buatAdmin();
        $kat = $this->buatKategori('Lab Multimedia');
        $file = UploadedFile::fake()->image('alat-khusus.png', 200, 200);

        $this->actingAs($admin)
            ->post(route('admin.alat.store'), [
                'nama_alat' => 'Alat Upload Kustom',
                'id_kategori' => $kat->id_kategori,
                'jumlah_tersedia' => 2,
                'illustration_mode' => 'upload',
                'illustration_file' => $file,
            ])
            ->assertRedirect(route('admin.alat.index'));

        $alat = Alat::where('nama_alat', 'Alat Upload Kustom')->firstOrFail();
        $override = AlatIllustrationService::getOverride($alat->id_alat);
        $this->assertSame('upload', $override['mode']);
        $this->assertFileExists(public_path('uploads/alat-testing/' . $override['file']));

        // Tampil sebagai <img> di halaman daftar alat & edit
        $this->actingAs($admin)
            ->get(route('admin.alat.index', ['q' => 'Alat Upload Kustom']))
            ->assertOk()
            ->assertSee($override['file']);

        // Hapus alat -> file upload & override ikut terhapus
        $this->actingAs($admin)
            ->delete(route('admin.alat.destroy', $alat))
            ->assertRedirect(route('admin.alat.index'));

        $this->assertFileDoesNotExist(public_path('uploads/alat-testing/' . $override['file']));
        $this->assertNull(AlatIllustrationService::getOverride($alat->id_alat));
    }

    public function test_admin_dapat_upload_dan_pilih_ikon_manual_untuk_kategori_serta_dibersihkan_saat_hapus(): void
    {
        $admin = $this->buatAdmin();
        $peminjam = User::create([
            'username' => 'pmj_kat_illu_' . uniqid(),
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.kategori.create'))
            ->assertOk()
            ->assertSee('Upload Gambar')
            ->assertSee('Pilih Ikon');

        $namaKategori = 'Lab Robotika ' . uniqid();
        $file = UploadedFile::fake()->image('robotika.png', 120, 120);

        $this->actingAs($admin)
            ->post(route('admin.kategori.store'), [
                'nama_kategori' => $namaKategori,
                'illustration_mode' => 'upload',
                'illustration_file' => $file,
            ])
            ->assertRedirect(route('admin.kategori.index'));

        $kategori = Kategori::where('nama_kategori', $namaKategori)->firstOrFail();
        $override = AlatIllustrationService::getOverride($kategori->id_kategori, 'kategori');
        $this->assertSame('upload', $override['mode']);
        $uploadedPath = public_path('uploads/kategori-testing/' . $override['file']);
        $this->assertFileExists($uploadedPath);

        // Tampil di daftar kategori Admin & katalog Peminjam
        $this->actingAs($admin)
            ->get(route('admin.kategori.index', ['q' => $namaKategori]))
            ->assertOk()
            ->assertSee($override['file']);

        $this->actingAs($peminjam)
            ->get(route('peminjam.katalog.index'))
            ->assertOk()
            ->assertSee($override['file']);

        // Ubah ke ikon manual -> file upload lama terhapus & berganti ikon manual
        $this->actingAs($admin)
            ->put(route('admin.kategori.update', $kategori), [
                'nama_kategori' => $namaKategori,
                'illustration_mode' => 'manual_icon',
                'illustration_icon' => 'penggaris',
            ])
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertFileDoesNotExist($uploadedPath);
        $updatedOverride = AlatIllustrationService::getOverride($kategori->id_kategori, 'kategori');
        $this->assertSame('manual_icon', $updatedOverride['mode']);
        $this->assertSame('penggaris', $updatedOverride['type']);

        // Hapus kategori -> override dibersihkan
        $this->actingAs($admin)
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertNull(AlatIllustrationService::getOverride($kategori->id_kategori, 'kategori'));
    }
}
