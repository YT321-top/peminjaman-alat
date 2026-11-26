<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PetugasModulTest extends TestCase
{
    use DatabaseTransactions;

    private function buatUser(string $role, string $prefix): User
    {
        return User::create([
            'username' => $prefix . '_' . uniqid(),
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function buatAlat(string $nama, int $stok): Alat
    {
        $kategori = Kategori::first() ?? Kategori::create([
            'nama_kategori' => 'Lab Uji ' . uniqid(),
        ]);

        return Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => $nama,
            'jumlah_tersedia' => $stok,
        ]);
    }

    public function test_hanya_petugas_yang_dapat_mengakses_modul_petugas(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $peminjam = $this->buatUser('peminjam', 'pmj');

        $this->get('/petugas/dashboard')->assertRedirect('/login');
        $this->get('/petugas/persetujuan')->assertRedirect('/login');
        $this->get('/petugas/pengembalian')->assertRedirect('/login');
        $this->get('/petugas/laporan')->assertRedirect('/login');
        $this->get('/petugas/laporan/cetak')->assertRedirect('/login');
        $this->get('/petugas/laporan/export-csv')->assertRedirect('/login');

        $this->actingAs($admin)->get('/petugas/persetujuan')->assertForbidden();
        $this->actingAs($admin)->get('/petugas/pengembalian')->assertForbidden();
        $this->actingAs($admin)->get('/petugas/laporan')->assertForbidden();

        $this->actingAs($peminjam)->get('/petugas/persetujuan')->assertForbidden();
        $this->actingAs($peminjam)->get('/petugas/pengembalian')->assertForbidden();
        $this->actingAs($peminjam)->get('/petugas/laporan')->assertForbidden();
        $this->actingAs($peminjam)->get('/petugas/laporan/cetak')->assertForbidden();
        $this->actingAs($peminjam)->get('/petugas/laporan/export-csv')->assertForbidden();
    }

    public function test_dashboard_petugas_menampilkan_statistik_dan_tautan_nyata(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');

        $response = $this->actingAs($petugas)->get('/petugas/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Petugas');
        $response->assertSee(route('petugas.persetujuan.index'), false);
        $response->assertSee(route('petugas.pengembalian.index'), false);
        $response->assertSee(route('petugas.laporan.index'), false);
    }

    public function test_persetujuan_index_filter_tab_pencarian_dan_indikator_stok_kurang(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'uji_cari');
        $alat = $this->buatAlat('Alat Uji Kurang', 2);

        $pinjamMenunggu = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjamMenunggu->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 5,
        ]);

        // Default tab menunggu + peringatan stok kurang
        $resDefault = $this->actingAs($petugas)->get('/petugas/persetujuan');
        $resDefault->assertOk();
        $resDefault->assertSee('#PMJ-' . $pinjamMenunggu->id_peminjaman);
        $resDefault->assertSee('stok kurang');

        // Pencarian berdasarkan kode #PMJ-{id}
        $resKode = $this->actingAs($petugas)->get('/petugas/persetujuan?status=menunggu&q=%23PMJ-' . $pinjamMenunggu->id_peminjaman);
        $resKode->assertOk();
        $resKode->assertSee($peminjam->username);

        // Pencarian berdasarkan username (case-insensitive di PostgreSQL)
        $resUser = $this->actingAs($petugas)->get('/petugas/persetujuan?status=semua&q=' . strtoupper($peminjam->username));
        $resUser->assertOk();
        $resUser->assertSee('#PMJ-' . $pinjamMenunggu->id_peminjaman);

        // Tab disetujui & ditolak tidak boleh error
        $this->actingAs($petugas)->get('/petugas/persetujuan?status=disetujui')->assertOk();
        $this->actingAs($petugas)->get('/petugas/persetujuan?status=ditolak')->assertOk();
    }

    public function test_setujui_peminjaman_mengurangi_stok_dan_mencatat_log(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alatA = $this->buatAlat('Mikroskop Uji A', 10);
        $alatB = $this->buatAlat('Multimeter Uji B', 6);

        $peminjaman = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alatA->id_alat,
            'jumlah_pinjam' => 3,
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alatB->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        $response = $this->actingAs($petugas)
            ->from('/petugas/persetujuan')
            ->post(route('petugas.persetujuan.setujui', $peminjaman));

        $response->assertRedirect('/petugas/persetujuan');
        $response->assertSessionHas('success');

        $peminjaman->refresh();
        $alatA->refresh();
        $alatB->refresh();

        $this->assertSame('disetujui', $peminjaman->status);
        $this->assertSame($petugas->id_user, $peminjaman->id_petugas);
        $this->assertSame(7, $alatA->jumlah_tersedia);
        $this->assertSame(4, $alatB->jumlah_tersedia);

        $this->assertTrue(
            LogAktivitas::where('id_user', $petugas->id_user)
                ->where('aksi', 'LIKE', "%#PMJ-{$peminjaman->id_peminjaman}%")
                ->where('aksi', 'LIKE', "%{$peminjam->username}%")
                ->exists()
        );
    }

    public function test_setujui_peminjaman_rollback_saat_satu_alat_stok_tidak_cukup(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alatCukup = $this->buatAlat('Gelas Ukur Cukup', 10);
        $alatKurang = $this->buatAlat('Osiloskop Stok Tipis', 1);

        $peminjaman = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alatCukup->id_alat,
            'jumlah_pinjam' => 4,
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alatKurang->id_alat,
            'jumlah_pinjam' => 3,
        ]);

        $response = $this->actingAs($petugas)
            ->from('/petugas/persetujuan')
            ->post(route('petugas.persetujuan.setujui', $peminjaman));

        $response->assertRedirect('/petugas/persetujuan');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Osiloskop Stok Tipis', session('error'));

        $peminjaman->refresh();
        $alatCukup->refresh();
        $alatKurang->refresh();

        $this->assertSame('menunggu', $peminjaman->status);
        $this->assertNull($peminjaman->id_petugas);
        $this->assertSame(10, $alatCukup->jumlah_tersedia);
        $this->assertSame(1, $alatKurang->jumlah_tersedia);
    }

    public function test_tolak_peminjaman_tidak_mengubah_stok_dan_mencatat_log(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alat = $this->buatAlat('Proyektor Uji Tolak', 5);

        $peminjaman = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        $response = $this->actingAs($petugas)
            ->from('/petugas/persetujuan')
            ->post(route('petugas.persetujuan.tolak', $peminjaman));

        $response->assertRedirect('/petugas/persetujuan');
        $response->assertSessionHas('success');

        $peminjaman->refresh();
        $alat->refresh();

        $this->assertSame('ditolak', $peminjaman->status);
        $this->assertSame($petugas->id_user, $peminjaman->id_petugas);
        $this->assertSame(5, $alat->jumlah_tersedia);

        $this->assertTrue(
            LogAktivitas::where('id_user', $petugas->id_user)
                ->where('aksi', 'LIKE', "%Menolak peminjaman #PMJ-{$peminjaman->id_peminjaman}%")
                ->exists()
        );

        // Tidak boleh disetujui lagi setelah ditolak
        $second = $this->actingAs($petugas)
            ->from('/petugas/persetujuan')
            ->post(route('petugas.persetujuan.setujui', $peminjaman));

        $second->assertSessionHas('error');
        $this->assertSame('ditolak', $peminjaman->fresh()->status);
    }

    public function test_pengembalian_konfirmasi_untuk_pengajuan_peminjam_dan_denda_override_manual(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alat = $this->buatAlat('Kamera Uji Kembali', 4);

        $peminjaman = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(5),
            'status' => 'disetujui',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        Pengembalian::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_petugas' => null,
            'tanggal_kembali' => today(),
            'denda' => 0,
        ]);

        // Tampil di tab Menunggu Verifikasi dengan saran keterlambatan 2 hari = Rp 4.000
        $resIndex = $this->actingAs($petugas)->get('/petugas/pengembalian?tab=verifikasi');
        $resIndex->assertOk();
        $resIndex->assertSee('#PMJ-' . $peminjaman->id_peminjaman);
        $resIndex->assertSee('Terlambat 2 hari');
        $resIndex->assertSee('Rp 4.000');

        // Konfirmasi dengan denda diubah manual (misal denda kerusakan Rp 12.000)
        $resKonfirmasi = $this->actingAs($petugas)
            ->from('/petugas/pengembalian?tab=verifikasi')
            ->post(route('petugas.pengembalian.konfirmasi', $peminjaman), [
                'tanggal_kembali' => today()->toDateString(),
                'denda' => 12000,
            ]);

        $resKonfirmasi->assertRedirect('/petugas/pengembalian?tab=verifikasi');
        $resKonfirmasi->assertSessionHas('success');

        $peminjaman->refresh();
        $alat->refresh();
        $pengembalian = Pengembalian::where('id_peminjaman', $peminjaman->id_peminjaman)->first();

        $this->assertSame('selesai', $peminjaman->status);
        $this->assertSame(6, $alat->jumlah_tersedia);
        $this->assertNotNull($pengembalian);
        $this->assertSame(1, Pengembalian::where('id_peminjaman', $peminjaman->id_peminjaman)->count());
        $this->assertSame($petugas->id_user, $pengembalian->id_petugas);
        $this->assertSame(12000, (int) $pengembalian->denda);

        $this->assertTrue(
            LogAktivitas::where('id_user', $petugas->id_user)
                ->where('aksi', 'LIKE', "%#PMJ-{$peminjaman->id_peminjaman}%")
                ->where('aksi', 'LIKE', '%Rp 12.000%')
                ->exists()
        );
    }

    public function test_pengembalian_konfirmasi_langsung_tanpa_pengajuan_peminjam_dan_validasi(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alat = $this->buatAlat('Tang Crimping Uji', 7);

        $peminjaman = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(2),
            'status' => 'disetujui',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 3,
        ]);

        // Tampil di tab Sedang Dipinjam
        $resDipinjam = $this->actingAs($petugas)->get('/petugas/pengembalian?tab=dipinjam');
        $resDipinjam->assertOk();
        $resDipinjam->assertSee('#PMJ-' . $peminjaman->id_peminjaman);
        $resDipinjam->assertSee('Tepat waktu');

        // Tolak denda negatif
        $this->actingAs($petugas)
            ->from('/petugas/pengembalian?tab=dipinjam')
            ->post(route('petugas.pengembalian.konfirmasi', $peminjaman), [
                'tanggal_kembali' => today()->toDateString(),
                'denda' => -1000,
            ])
            ->assertSessionHasErrors('denda');

        // Tolak tanggal kembali sebelum tanggal pinjam
        $this->actingAs($petugas)
            ->from('/petugas/pengembalian?tab=dipinjam')
            ->post(route('petugas.pengembalian.konfirmasi', $peminjaman), [
                'tanggal_kembali' => today()->subDays(5)->toDateString(),
                'denda' => 0,
            ])
            ->assertSessionHas('error');

        $this->assertSame('disetujui', $peminjaman->fresh()->status);

        // Konfirmasi langsung berhasil
        $this->actingAs($petugas)
            ->from('/petugas/pengembalian?tab=dipinjam')
            ->post(route('petugas.pengembalian.konfirmasi', $peminjaman), [
                'tanggal_kembali' => today()->toDateString(),
                'denda' => 0,
            ])
            ->assertSessionHas('success');

        $this->assertSame('selesai', $peminjaman->fresh()->status);
        $this->assertSame(10, $alat->fresh()->jumlah_tersedia);
        $this->assertSame(1, Pengembalian::where('id_peminjaman', $peminjaman->id_peminjaman)->count());

        // Tab selesai menampilkan transaksi yang baru selesai
        $resSelesai = $this->actingAs($petugas)->get('/petugas/pengembalian?tab=selesai');
        $resSelesai->assertOk();
        $resSelesai->assertSee('#PMJ-' . $peminjaman->id_peminjaman);
        $resSelesai->assertSee($petugas->username);
    }

    public function test_laporan_index_cetak_a4_dan_export_csv_utf8_bom(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj_lap');
        $alat = $this->buatAlat('Router Uji Laporan', 5);

        $tanggalUji = '2026-08-15';
        $peminjaman = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => $tanggalUji,
            'status' => 'selesai',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        Pengembalian::create([
            'id_peminjaman' => $peminjaman->id_peminjaman,
            'id_petugas' => $petugas->id_user,
            'tanggal_kembali' => '2026-08-20',
            'denda' => 4000,
        ]);

        $params = [
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-31',
            'status' => 'selesai',
        ];

        // 1. Halaman Index Laporan
        $resIndex = $this->actingAs($petugas)->get(route('petugas.laporan.index', $params));
        $resIndex->assertOk();
        $resIndex->assertSee('#PMJ-' . $peminjaman->id_peminjaman);
        $resIndex->assertSee('Router Uji Laporan');
        $resIndex->assertSee('Rp 4.000');

        // 2. Halaman Cetak A4
        $resCetak = $this->actingAs($petugas)->get(route('petugas.laporan.cetak', $params));
        $resCetak->assertOk();
        $resCetak->assertSee('Laporan Peminjaman Alat Laboratorium Sekolah');
        $resCetak->assertSee('Cetak / Simpan PDF');
        $resCetak->assertSee('print:hidden', false);
        $resCetak->assertSee('Petugas Laboratorium');
        $resCetak->assertSee('#PMJ-' . $peminjaman->id_peminjaman);
        $resCetak->assertSee('Rp 4.000');

        // 3. Export CSV dengan UTF-8 BOM
        $resCsv = $this->actingAs($petugas)->get(route('petugas.laporan.export-csv', $params));
        $resCsv->assertOk();
        $this->assertStringContainsString('text/csv', (string) $resCsv->headers->get('Content-Type'));

        $csvContent = $resCsv->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvContent);
        $this->assertStringContainsString('#PMJ-' . $peminjaman->id_peminjaman, $csvContent);
        $this->assertStringContainsString('Router Uji Laporan (2 unit)', $csvContent);
        $this->assertStringContainsString('4000', $csvContent);
    }
}
