<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $passwordHash = Hash::make('password');

        // 1. Akun Default (password: "password")
        $admin = User::create([
            'username' => 'admin',
            'password' => $passwordHash,
            'role' => 'admin',
        ]);

        $petugas = User::create([
            'username' => 'petugas',
            'password' => $passwordHash,
            'role' => 'petugas',
        ]);

        $peminjam = User::create([
            'username' => 'peminjam',
            'password' => $passwordHash,
            'role' => 'peminjam',
        ]);

        $siswaBudi = User::create([
            'username' => 'siswa_budi',
            'password' => $passwordHash,
            'role' => 'peminjam',
        ]);

        // 2. 5 Kategori Lab Sekolah
        $katKimia = Kategori::create(['nama_kategori' => 'Lab Kimia']);
        $katFisika = Kategori::create(['nama_kategori' => 'Lab Fisika']);
        $katBiologi = Kategori::create(['nama_kategori' => 'Lab Biologi']);
        $katKomputer = Kategori::create(['nama_kategori' => 'Lab Komputer & Jaringan']);
        $katMultimedia = Kategori::create(['nama_kategori' => 'Peralatan Multimedia']);

        // 3. 15 Alat Lab Sekolah Realistis
        $gelasUkur = Alat::create([
            'id_kategori' => $katKimia->id_kategori,
            'nama_alat' => 'Gelas Ukur 500ml',
            'jumlah_tersedia' => 15,
        ]);

        Alat::create([
            'id_kategori' => $katKimia->id_kategori,
            'nama_alat' => 'Tabung Reaksi Set',
            'jumlah_tersedia' => 20,
        ]);

        $labuErlenmeyer = Alat::create([
            'id_kategori' => $katKimia->id_kategori,
            'nama_alat' => 'Labu Erlenmeyer 250ml',
            'jumlah_tersedia' => 12,
        ]);

        $multimeter = Alat::create([
            'id_kategori' => $katFisika->id_kategori,
            'nama_alat' => 'Multimeter Digital',
            'jumlah_tersedia' => 10,
        ]);

        $osiloskop = Alat::create([
            'id_kategori' => $katFisika->id_kategori,
            'nama_alat' => 'Osiloskop Mini',
            'jumlah_tersedia' => 4,
        ]);

        $jangkaSorong = Alat::create([
            'id_kategori' => $katFisika->id_kategori,
            'nama_alat' => 'Jangka Sorong Digital',
            'jumlah_tersedia' => 14,
        ]);

        $mikroskop = Alat::create([
            'id_kategori' => $katBiologi->id_kategori,
            'nama_alat' => 'Mikroskop Binokuler',
            'jumlah_tersedia' => 8,
        ]);

        Alat::create([
            'id_kategori' => $katBiologi->id_kategori,
            'nama_alat' => 'Lup / Kaca Pembesar',
            'jumlah_tersedia' => 18,
        ]);

        Alat::create([
            'id_kategori' => $katBiologi->id_kategori,
            'nama_alat' => 'Kit Preparat Biologi',
            'jumlah_tersedia' => 10,
        ]);

        $tangCrimping = Alat::create([
            'id_kategori' => $katKomputer->id_kategori,
            'nama_alat' => 'Tang Crimping RJ45',
            'jumlah_tersedia' => 12,
        ]);

        $lanTester = Alat::create([
            'id_kategori' => $katKomputer->id_kategori,
            'nama_alat' => 'LAN Cable Tester',
            'jumlah_tersedia' => 10,
        ]);

        Alat::create([
            'id_kategori' => $katKomputer->id_kategori,
            'nama_alat' => 'Mikrotik RouterBoard RB941',
            'jumlah_tersedia' => 6,
        ]);

        $proyektor = Alat::create([
            'id_kategori' => $katMultimedia->id_kategori,
            'nama_alat' => 'Proyektor Epson XGA',
            'jumlah_tersedia' => 5,
        ]);

        $kameraDslr = Alat::create([
            'id_kategori' => $katMultimedia->id_kategori,
            'nama_alat' => 'Kamera DSLR Praktik',
            'jumlah_tersedia' => 3,
        ]);

        Alat::create([
            'id_kategori' => $katMultimedia->id_kategori,
            'nama_alat' => 'Tripod Aluminium',
            'jumlah_tersedia' => 6,
        ]);

        // 4. Data Transaksi Dummy (Semua Status: menunggu, disetujui, selesai, ditolak)

        // Peminjaman #1: menunggu (2 item, siap diuji Petugas Setujui/Tolak)
        $pinjam1 = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam1->id_peminjaman,
            'id_alat' => $gelasUkur->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam1->id_peminjaman,
            'id_alat' => $labuErlenmeyer->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        // Peminjaman #2: menunggu (1 item, siap diuji Petugas Setujui/Tolak)
        $pinjam2 = Peminjaman::create([
            'id_user' => $siswaBudi->id_user,
            'id_petugas' => null,
            'tanggal_pinjam' => today(),
            'status' => 'menunggu',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam2->id_peminjaman,
            'id_alat' => $mikroskop->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        // Peminjaman #3: disetujui, belum diajukan kembali (siap diuji tombol Kembalikan Alat oleh peminjam)
        $pinjam3 = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(2),
            'status' => 'disetujui',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam3->id_peminjaman,
            'id_alat' => $multimeter->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam3->id_peminjaman,
            'id_alat' => $jangkaSorong->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        // Peminjaman #4: disetujui, sudah diajukan kembali (id_petugas = null, pinjam 5 hari lalu -> estimasi denda keterlambatan 2 hari)
        $pinjam4 = Peminjaman::create([
            'id_user' => $siswaBudi->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(5),
            'status' => 'disetujui',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam4->id_peminjaman,
            'id_alat' => $osiloskop->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        Pengembalian::create([
            'id_peminjaman' => $pinjam4->id_peminjaman,
            'id_petugas' => null,
            'tanggal_kembali' => today(),
            'denda' => 0,
        ]);

        // Peminjaman #5: selesai (tepat waktu, tanpa denda)
        $pinjam5 = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(10),
            'status' => 'selesai',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam5->id_peminjaman,
            'id_alat' => $tangCrimping->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam5->id_peminjaman,
            'id_alat' => $lanTester->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        Pengembalian::create([
            'id_peminjaman' => $pinjam5->id_peminjaman,
            'id_petugas' => $petugas->id_user,
            'tanggal_kembali' => today()->subDays(8),
            'denda' => 0,
        ]);

        // Peminjaman #6: selesai (terlambat 2 hari, denda Rp 4.000)
        $pinjam6 = Peminjaman::create([
            'id_user' => $siswaBudi->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(6),
            'status' => 'selesai',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam6->id_peminjaman,
            'id_alat' => $proyektor->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        Pengembalian::create([
            'id_peminjaman' => $pinjam6->id_peminjaman,
            'id_petugas' => $petugas->id_user,
            'tanggal_kembali' => today()->subDay(),
            'denda' => 4000,
        ]);

        // Peminjaman #7: ditolak
        $pinjam7 = Peminjaman::create([
            'id_user' => $siswaBudi->id_user,
            'id_petugas' => $petugas->id_user,
            'tanggal_pinjam' => today()->subDays(3),
            'status' => 'ditolak',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $pinjam7->id_peminjaman,
            'id_alat' => $kameraDslr->id_alat,
            'jumlah_pinjam' => 2,
        ]);

        // 5. Log Aktivitas Awal
        LogAktivitas::create([
            'id_user' => $admin->id_user,
            'aksi' => 'Menyiapkan data master kategori dan inventaris alat laboratorium',
            'waktu' => now()->subDays(11),
        ]);

        LogAktivitas::create([
            'id_user' => $petugas->id_user,
            'aksi' => "Menolak pengajuan peminjaman #{$pinjam7->id_peminjaman} milik siswa_budi",
            'waktu' => now()->subDays(3),
        ]);

        LogAktivitas::create([
            'id_user' => $petugas->id_user,
            'aksi' => "Menyetujui pengajuan peminjaman #{$pinjam3->id_peminjaman} milik peminjam",
            'waktu' => now()->subDays(2),
        ]);

        LogAktivitas::create([
            'id_user' => $petugas->id_user,
            'aksi' => "Mengonfirmasi pengembalian peminjaman #{$pinjam6->id_peminjaman} dengan denda Rp 4.000",
            'waktu' => now()->subDay(),
        ]);

        LogAktivitas::create([
            'id_user' => $peminjam->id_user,
            'aksi' => "Mengajukan peminjaman #{$pinjam1->id_peminjaman} (Gelas Ukur 500ml, Labu Erlenmeyer 250ml)",
            'waktu' => now()->subHours(4),
        ]);

        LogAktivitas::create([
            'id_user' => $siswaBudi->id_user,
            'aksi' => "Mengajukan pengembalian alat untuk peminjaman #{$pinjam4->id_peminjaman}",
            'waktu' => now()->subHours(2),
        ]);
    }
}
