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

class AdminModulTest extends TestCase
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

    private function buatKategori(string $nama): Kategori
    {
        return Kategori::create([
            'nama_kategori' => $nama . ' ' . uniqid(),
        ]);
    }

    private function buatAlat(string $nama, int $stok, ?Kategori $kategori = null): Alat
    {
        $kat = $kategori ?? $this->buatKategori('Kat Uji');

        return Alat::create([
            'id_kategori' => $kat->id_kategori,
            'nama_alat' => $nama,
            'jumlah_tersedia' => $stok,
        ]);
    }

    public function test_hanya_admin_yang_dapat_mengakses_modul_admin(): void
    {
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');

        $urls = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/kategori',
            '/admin/alat',
            '/admin/peminjaman',
            '/admin/pengembalian',
            '/admin/log-aktivitas',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        foreach ($urls as $url) {
            $this->actingAs($petugas)->get($url)->assertForbidden();
        }

        foreach ($urls as $url) {
            $this->actingAs($peminjam)->get($url)->assertForbidden();
        }
    }

    public function test_dashboard_admin_dan_tautan_menu_nyata(): void
    {
        $admin = $this->buatUser('admin', 'adm');

        $res = $this->actingAs($admin)->get('/admin/dashboard');

        $res->assertOk();
        $res->assertSee('Dashboard Admin');
        $res->assertSee(route('admin.users.index'), false);
        $res->assertSee(route('admin.kategori.index'), false);
        $res->assertSee(route('admin.alat.index'), false);
        $res->assertSee(route('admin.peminjaman.index'), false);
        $res->assertSee(route('admin.pengembalian.index'), false);
        $res->assertSee(route('admin.log-aktivitas.index'), false);
    }

    public function test_crud_user_lengkap_proteksi_akun_sendiri_dan_fk(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $usernameBaru = 'siswa_uji_' . uniqid();

        // Store user baru
        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'username' => $usernameBaru,
                'role' => 'peminjam',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $created = User::where('username', $usernameBaru)->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $created->password));
        $this->assertTrue(
            LogAktivitas::where('id_user', $admin->id_user)
                ->where('aksi', 'LIKE', "%{$usernameBaru}%")
                ->exists()
        );

        // Index + pencarian + filter role
        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'peminjam', 'q' => strtoupper($usernameBaru)]))
            ->assertOk()
            ->assertSee($usernameBaru);

        // Update tanpa mengubah password -> password lama tetap berlaku
        $usernameEdit = $usernameBaru . '_edit';
        $this->actingAs($admin)
            ->put(route('admin.users.update', $created), [
                'username' => $usernameEdit,
                'role' => 'petugas',
                'password' => '',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $created->refresh();
        $this->assertSame($usernameEdit, $created->username);
        $this->assertSame('petugas', $created->role);
        $this->assertTrue(Hash::check('rahasia123', $created->password));

        // Update dengan password baru
        $this->actingAs($admin)
            ->put(route('admin.users.update', $created), [
                'username' => $usernameEdit,
                'role' => 'peminjam',
                'password' => 'baru456',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('baru456', $created->fresh()->password));

        // Tidak boleh hapus akun sendiri
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(User::find($admin->id_user));

        // Tidak boleh hapus user yang masih punya peminjaman (tidak crash 500)
        $peminjamTerikat = $this->buatUser('peminjam', 'terikat');
        Peminjaman::create([
            'id_user' => $peminjamTerikat->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $peminjamTerikat))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(User::find($peminjamTerikat->id_user));

        // Hapus user bersih berhasil
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $created))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');
        $this->assertNull(User::find($created->id_user));
    }

    public function test_crud_kategori_dan_cegah_hapus_saat_dipakai_alat(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $namaKat = 'Lab Robotika ' . uniqid();

        $this->actingAs($admin)
            ->post(route('admin.kategori.store'), ['nama_kategori' => $namaKat])
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $kategori = Kategori::where('nama_kategori', $namaKat)->firstOrFail();

        // Duplikat ditolak
        $this->actingAs($admin)
            ->post(route('admin.kategori.store'), ['nama_kategori' => $namaKat])
            ->assertSessionHasErrors('nama_kategori');

        // Edit kategori
        $namaBaru = $namaKat . ' Baru';
        $this->actingAs($admin)
            ->put(route('admin.kategori.update', $kategori), ['nama_kategori' => $namaBaru])
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');

        $this->assertSame($namaBaru, $kategori->fresh()->nama_kategori);

        // Tambah 1 alat ke kategori -> cegah hapus
        $alat = $this->buatAlat('Sensor Ultrasonik', 5, $kategori);

        $this->actingAs($admin)
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(Kategori::find($kategori->id_kategori));

        // Setelah alat dihapus, kategori bisa dihapus
        $alat->delete();
        $this->actingAs($admin)
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('success');
        $this->assertNull(Kategori::find($kategori->id_kategori));
    }

    public function test_crud_alat_dan_cegah_hapus_saat_ada_riwayat_peminjaman(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $kategori = $this->buatKategori('Lab Elektro');

        $this->actingAs($admin)
            ->post(route('admin.alat.store'), [
                'nama_alat' => 'Solder Digital Uji',
                'id_kategori' => $kategori->id_kategori,
                'jumlah_tersedia' => 0,
            ])
            ->assertRedirect(route('admin.alat.index'))
            ->assertSessionHas('success');

        $alat = Alat::where('nama_alat', 'Solder Digital Uji')->firstOrFail();

        // Indikator habis tampil saat stok 0
        $this->actingAs($admin)
            ->get(route('admin.alat.index', ['kategori' => $kategori->id_kategori, 'q' => 'solder']))
            ->assertOk()
            ->assertSee('Solder Digital Uji')
            ->assertSee('0 unit (Habis)');

        // Update stok
        $this->actingAs($admin)
            ->put(route('admin.alat.update', $alat), [
                'nama_alat' => 'Solder Digital Pro',
                'id_kategori' => $kategori->id_kategori,
                'jumlah_tersedia' => 8,
            ])
            ->assertRedirect(route('admin.alat.index'))
            ->assertSessionHas('success');

        $this->assertSame(8, $alat->fresh()->jumlah_tersedia);

        // Kaitkan dengan detail_peminjaman -> cegah hapus
        $pinjam = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.alat.destroy', $alat))
            ->assertRedirect(route('admin.alat.index'))
            ->assertSessionHas('error');
        $this->assertNotNull(Alat::find($alat->id_alat));

        // Alat tanpa riwayat bisa dihapus
        $alatBersih = $this->buatAlat('Alat Bersih Hapus', 3, $kategori);
        $this->actingAs($admin)
            ->delete(route('admin.alat.destroy', $alatBersih))
            ->assertRedirect(route('admin.alat.index'))
            ->assertSessionHas('success');
        $this->assertNull(Alat::find($alatBersih->id_alat));
    }

    public function test_crud_peminjaman_multi_item_dan_sinkronisasi_stok(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alatA = $this->buatAlat('Alat Sync A', 10);
        $alatB = $this->buatAlat('Alat Sync B', 5);

        // 1. Tambah status disetujui melebihi stok -> gagal & rollback penuh
        $this->actingAs($admin)
            ->from(route('admin.peminjaman.create'))
            ->post(route('admin.peminjaman.store'), [
                'id_user' => $peminjam->id_user,
                'id_petugas' => $petugas->id_user,
                'tanggal_pinjam' => today()->toDateString(),
                'status' => 'disetujui',
                'items' => [
                    ['id_alat' => $alatA->id_alat, 'jumlah_pinjam' => 3],
                    ['id_alat' => $alatB->id_alat, 'jumlah_pinjam' => 9],
                ],
            ])
            ->assertRedirect(route('admin.peminjaman.create'))
            ->assertSessionHas('error');

        $this->assertSame(10, $alatA->fresh()->jumlah_tersedia);
        $this->assertSame(5, $alatB->fresh()->jumlah_tersedia);

        // 2. Tambah status disetujui valid -> stok kedua alat berkurang
        $this->actingAs($admin)
            ->post(route('admin.peminjaman.store'), [
                'id_user' => $peminjam->id_user,
                'id_petugas' => $petugas->id_user,
                'tanggal_pinjam' => today()->toDateString(),
                'status' => 'disetujui',
                'items' => [
                    ['id_alat' => $alatA->id_alat, 'jumlah_pinjam' => 3],
                    ['id_alat' => $alatB->id_alat, 'jumlah_pinjam' => 2],
                ],
            ])
            ->assertRedirect(route('admin.peminjaman.index'))
            ->assertSessionHas('success');

        $pinjam = Peminjaman::where('id_user', $peminjam->id_user)->latest('id_peminjaman')->firstOrFail();
        $this->assertSame(7, $alatA->fresh()->jumlah_tersedia);
        $this->assertSame(3, $alatB->fresh()->jumlah_tersedia);

        // 3. Edit jumlah saat tetap disetujui (A: 3->5, B: dihapus) -> stok A jadi 5, stok B kembali jadi 5
        $this->actingAs($admin)
            ->put(route('admin.peminjaman.update', $pinjam), [
                'id_user' => $peminjam->id_user,
                'id_petugas' => $petugas->id_user,
                'tanggal_pinjam' => today()->toDateString(),
                'status' => 'disetujui',
                'items' => [
                    ['id_alat' => $alatA->id_alat, 'jumlah_pinjam' => 5],
                ],
            ])
            ->assertRedirect(route('admin.peminjaman.index'))
            ->assertSessionHas('success');

        $this->assertSame(5, $alatA->fresh()->jumlah_tersedia);
        $this->assertSame(5, $alatB->fresh()->jumlah_tersedia);

        // 4. Edit melebihi stok -> rollback, stok lama tetap terjaga (A tetap 5)
        $this->actingAs($admin)
            ->from(route('admin.peminjaman.edit', $pinjam))
            ->put(route('admin.peminjaman.update', $pinjam), [
                'id_user' => $peminjam->id_user,
                'id_petugas' => $petugas->id_user,
                'tanggal_pinjam' => today()->toDateString(),
                'status' => 'disetujui',
                'items' => [
                    ['id_alat' => $alatA->id_alat, 'jumlah_pinjam' => 20],
                ],
            ])
            ->assertRedirect(route('admin.peminjaman.edit', $pinjam))
            ->assertSessionHas('error');

        $this->assertSame(5, $alatA->fresh()->jumlah_tersedia);

        // 5. Hapus peminjaman berstatus disetujui -> stok A kembali utuh jadi 10
        $this->actingAs($admin)
            ->delete(route('admin.peminjaman.destroy', $pinjam))
            ->assertRedirect(route('admin.peminjaman.index'))
            ->assertSessionHas('success');

        $this->assertNull(Peminjaman::find($pinjam->id_peminjaman));
        $this->assertSame(10, $alatA->fresh()->jumlah_tersedia);
    }

    public function test_crud_pengembalian_dan_sinkronisasi_stok_saat_tambah_edit_hapus(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $petugas = $this->buatUser('petugas', 'ptg');
        $peminjam = $this->buatUser('peminjam', 'pmj');
        $alat = $this->buatAlat('Mikrotik Uji Kembali', 4);

        $pinjam = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(5),
            'status' => 'disetujui',
        ]);
        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam->id_peminjaman,
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        // 1. Tambah pengembalian -> stok bertambah (4 -> 6) dan status jadi selesai
        $this->actingAs($admin)
            ->post(route('admin.pengembalian.store'), [
                'id_peminjaman' => $pinjam->id_peminjaman,
                'id_petugas' => $petugas->id_user,
                'tanggal_kembali' => today()->toDateString(),
                'denda' => 4000,
            ])
            ->assertRedirect(route('admin.pengembalian.index'))
            ->assertSessionHas('success');

        $pengembalian = Pengembalian::where('id_peminjaman', $pinjam->id_peminjaman)->firstOrFail();
        $this->assertSame('selesai', $pinjam->fresh()->status);
        $this->assertSame(6, $alat->fresh()->jumlah_tersedia);
        $this->assertSame(4000, (int) $pengembalian->denda);

        // 2. Edit pengembalian -> ubah denda jadi 1500 & petugas jadi admin, stok tetap 6
        $this->actingAs($admin)
            ->put(route('admin.pengembalian.update', $pengembalian), [
                'id_petugas' => $admin->id_user,
                'tanggal_kembali' => today()->toDateString(),
                'denda' => 1500,
            ])
            ->assertRedirect(route('admin.pengembalian.index'))
            ->assertSessionHas('success');

        $pengembalian->refresh();
        $this->assertSame(1500, (int) $pengembalian->denda);
        $this->assertSame($admin->id_user, $pengembalian->id_petugas);
        $this->assertSame(6, $alat->fresh()->jumlah_tersedia);

        // 3. Simulasi stok kurang saat hapus pengembalian -> ditolak aman
        $alat->jumlah_tersedia = 1;
        $alat->save();

        $this->actingAs($admin)
            ->delete(route('admin.pengembalian.destroy', $pengembalian))
            ->assertRedirect(route('admin.pengembalian.index'))
            ->assertSessionHas('error');

        $this->assertNotNull(Pengembalian::find($pengembalian->id_pengembalian));
        $this->assertSame('selesai', $pinjam->fresh()->status);
        $this->assertSame(1, $alat->fresh()->jumlah_tersedia);

        // 4. Kembalikan stok cukup (6) lalu hapus pengembalian -> status kembali disetujui & stok jadi 4
        $alat->jumlah_tersedia = 6;
        $alat->save();

        $this->actingAs($admin)
            ->delete(route('admin.pengembalian.destroy', $pengembalian))
            ->assertRedirect(route('admin.pengembalian.index'))
            ->assertSessionHas('success');

        $this->assertNull(Pengembalian::find($pengembalian->id_pengembalian));
        $this->assertSame('disetujui', $pinjam->fresh()->status);
        $this->assertSame(4, $alat->fresh()->jumlah_tersedia);
    }

    public function test_log_aktivitas_read_only_dengan_berbagai_filter(): void
    {
        $admin = $this->buatUser('admin', 'adm');
        $petugas = $this->buatUser('petugas', 'ptg_log');
        $kodeUnik = 'AKSI_UNIK_' . uniqid();

        LogAktivitas::create([
            'id_user' => $petugas->id_user,
            'aksi' => "Pengujian {$kodeUnik} oleh petugas",
            'waktu' => now(),
        ]);

        $res = $this->actingAs($admin)->get(route('admin.log-aktivitas.index', [
            'id_user' => $petugas->id_user,
            'role' => 'petugas',
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_selesai' => today()->toDateString(),
            'q' => strtolower($kodeUnik),
        ]));

        $res->assertOk();
        $res->assertSee($kodeUnik);
        $res->assertSee($petugas->username);
        $res->assertDontSee('Tambah Log');
        $res->assertDontSee('Hapus Log');
    }
}
