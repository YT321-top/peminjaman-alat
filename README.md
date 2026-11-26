# LabPinjam — Aplikasi Peminjaman Alat Lab Sekolah

Aplikasi web manajemen peminjaman dan pengembalian alat laboratorium sekolah berbasis **Laravel 10**, **PostgreSQL**, dan **Tailwind CSS v3**.

## Prasyarat

- PHP >= 8.1 (pastikan ekstensi `pdo_pgsql` dan `pgsql` aktif di `php.ini`)
- Composer
- Node.js & npm
- PostgreSQL

## Instalasi Setelah `git clone`

1. **Clone repositori dan masuk ke folder proyek:**
   ```bash
   git clone https://github.com/YT321-top/peminjaman-alat.git
   cd peminjaman-alat
   ```

2. **Install dependensi PHP dan JavaScript, lalu build aset:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Buat database kosong di PostgreSQL:**
   Buat database bernama `db_peminjaman_alat` (melalui pgAdmin, DBeaver, atau `psql`).

4. **Konfigurasi file `.env`:**
   Salin `.env.example` menjadi `.env`:
   - Windows (CMD / PowerShell):
     ```bash
     copy .env.example .env
     ```
   - macOS / Linux:
     ```bash
     cp .env.example .env
     ```
   Lalu generate application key:
   ```bash
   php artisan key:generate
   ```
   Buka file `.env` dan sesuaikan `DB_USERNAME` serta `DB_PASSWORD` dengan akun PostgreSQL lokal Anda.

5. **Jalankan migrasi dan seeder:**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan server lokal:**
   ```bash
   php artisan serve
   ```
   Buka aplikasi di browser pada `http://127.0.0.1:8000`.

## Akun Default (Seeder)

Semua akun di bawah ini menggunakan password: `password`

| Username | Role | Akses Utama |
| --- | --- | --- |
| `admin` | `admin` | Dashboard, CRUD User, Kategori, Alat, Peminjaman, Pengembalian, Log Aktivitas |
| `petugas` | `petugas` | Dashboard, Persetujuan Peminjaman, Konfirmasi Pengembalian & Denda, Laporan & Export CSV |
| `peminjam` | `peminjam` | Katalog Alat Lab, Daftar Pinjam, Peminjaman Saya & Ajukan Pengembalian |
| `siswa_budi` | `peminjam` | Katalog Alat Lab, Daftar Pinjam, Peminjaman Saya & Ajukan Pengembalian |
