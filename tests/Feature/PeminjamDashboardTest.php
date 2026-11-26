<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamDashboardTest extends TestCase
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

    public function test_login_peminjam_redirect_ke_peminjam_dashboard(): void
    {
        $peminjam = $this->buatUser('peminjam', 'pmj_login');

        $response = $this->post('/login', [
            'username' => $peminjam->username,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($peminjam);

        // Root '/' juga mengarah ke peminjam.dashboard
        $this->actingAs($peminjam)->get('/')->assertRedirect(route('peminjam.dashboard'));
    }

    public function test_proteksi_role_peminjam_dashboard(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $petugas = $this->buatUser('petugas', 'ptg');

        $this->get('/peminjam/dashboard')->assertRedirect('/login');
        $this->actingAs($admin)->get('/peminjam/dashboard')->assertForbidden();
        $this->actingAs($petugas)->get('/peminjam/dashboard')->assertForbidden();
    }

    public function test_dashboard_peminjam_dinamis_navigasi_statistik_dan_tambah_daftar_pinjam(): void
    {
        $peminjam = $this->buatUser('peminjam', 'pmj_dash');
        $peminjamLain = $this->buatUser('peminjam', 'pmj_lain');
        $kategori = Kategori::first() ?? Kategori::create(['nama_kategori' => 'Lab Uji ' . uniqid()]);

        $alatTersedia = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Alat Populer Tersedia ' . uniqid(),
            'jumlah_tersedia' => 10,
        ]);

        $alatMenipis = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Alat Populer Menipis ' . uniqid(),
            'jumlah_tersedia' => 2,
        ]);

        $alatHabis = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Alat Populer Habis ' . uniqid(),
            'jumlah_tersedia' => 0,
        ]);

        // Buat 1 disetujui, 2 menunggu, 1 selesai milik $peminjam (total 4 transaksi)
        $pinjamAktif = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'disetujui',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $pinjamAktif->id_peminjaman,
            'id_alat' => $alatTersedia->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        for ($i = 0; $i < 2; $i++) {
            $pm = Peminjaman::create([
                'id_user' => $peminjam->id_user,
                'id_petugas' => null,
                'tanggal_pinjam' => today(),
                'status' => 'menunggu',
            ]);
            DetailPeminjaman::create([
                'id_peminjaman' => $pm->id_peminjaman,
                'id_alat' => $alatTersedia->id_alat,
                'jumlah_pinjam' => 1,
            ]);
        }

        $pinjamSelesai = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today()->subDays(4),
            'status' => 'selesai',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $pinjamSelesai->id_peminjaman,
            'id_alat' => $alatMenipis->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        // Tambah banyak riwayat pada alat uji agar pasti masuk 5 terpopuler
        for ($j = 0; $j < 6; $j++) {
            $pOther = Peminjaman::create([
                'id_user' => $peminjamLain->id_user,
                'id_petugas' => null,
                'tanggal_pinjam' => today()->subDays(2),
                'status' => 'selesai',
            ]);
            DetailPeminjaman::create([
                'id_peminjaman' => $pOther->id_peminjaman,
                'id_alat' => $alatTersedia->id_alat,
                'jumlah_pinjam' => 1,
            ]);
            DetailPeminjaman::create([
                'id_peminjaman' => $pOther->id_peminjaman,
                'id_alat' => $alatMenipis->id_alat,
                'jumlah_pinjam' => 1,
            ]);
            DetailPeminjaman::create([
                'id_peminjaman' => $pOther->id_peminjaman,
                'id_alat' => $alatHabis->id_alat,
                'jumlah_pinjam' => 1,
            ]);
        }

        $res = $this->actingAs($peminjam)->get(route('peminjam.dashboard'));

        $res->assertOk();
        $res->assertSeeInOrder(['Dashboard', 'Katalog Alat', 'Daftar Pinjam', 'Peminjaman Saya']);
        $res->assertSee('1 Transaksi');
        $res->assertSee('2 Pengajuan');
        $res->assertSee('4 Transaksi');
        $res->assertSee('1 selesai dikembalikan');
        $res->assertSee('Alat Laboratorium Terpopuler');
        $res->assertSee($alatTersedia->nama_alat);
        $res->assertSee($alatMenipis->nama_alat);
        $res->assertSee($alatHabis->nama_alat);
        $res->assertSee('Tersedia');
        $res->assertSee('Stok Menipis');
        $res->assertSee('Habis');
        $res->assertSee('Panduan Peminjaman Lab');
        $res->assertSee(route('peminjam.daftar-pinjam.tambah'), false);

        // Route /dashboard juga menampilkan halaman yang sama
        $this->actingAs($peminjam)->get('/dashboard')->assertOk()->assertSee('Alat Laboratorium Terpopuler');

        // Klik + Pinjam dari dashboard terhubung ke session Daftar Pinjam
        $addRes = $this->actingAs($peminjam)
            ->from(route('peminjam.dashboard'))
            ->post(route('peminjam.daftar-pinjam.tambah'), [
                'id_alat' => $alatTersedia->id_alat,
                'jumlah' => 1,
            ]);

        $addRes->assertRedirect(route('peminjam.dashboard'));
        $addRes->assertSessionHas('daftar_pinjam', [$alatTersedia->id_alat => 1]);
        $addRes->assertSessionHas('added_item');

        // Setelah ditambah, tombol CTA Daftar Pinjam menunjukkan (1)
        $this->actingAs($peminjam)
            ->get(route('peminjam.dashboard'))
            ->assertOk()
            ->assertSee('Daftar Pinjam (1)');
    }
}
