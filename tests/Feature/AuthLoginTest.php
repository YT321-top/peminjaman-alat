<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
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

    public function test_halaman_login_menampilkan_elemen_wajib_dan_env_demo_hanya_di_local(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('id="login-shell"', false);
        $response->assertSee('id="visual-showcase"', false);
        $response->assertSee('id="login-alert"', false);
        $response->assertSee('id="help-modal"', false);
        $response->assertSee('name="username"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="_token"', false);

        // Di phpunit.xml APP_ENV=testing (bukan local), bagian akun demo tidak muncul
        $response->assertDontSee('Atau isi cepat akun demo');
    }

    public function test_login_berhasil_mengarahkan_sesuai_role_database(): void
    {
        $admin = $this->buatUser('admin', 'adm_auth');
        $petugas = $this->buatUser('petugas', 'ptg_auth');
        $peminjam = $this->buatUser('peminjam', 'pmj_auth');

        // 1. Admin -> /admin/dashboard
        $resAdmin = $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
        ]);
        $resAdmin->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/dashboard')->assertOk();
        $this->assertTrue(
            LogAktivitas::where('id_user', $admin->id_user)
                ->where('aksi', 'Login ke sistem')
                ->exists()
        );
        $this->post('/logout')->assertRedirect('/login');

        // 2. Petugas -> /petugas/peminjaman
        $resPetugas = $this->post('/login', [
            'username' => $petugas->username,
            'password' => 'password',
        ]);
        $resPetugas->assertRedirect('/petugas/peminjaman');
        $this->assertAuthenticatedAs($petugas);
        $this->get('/petugas/peminjaman')->assertOk();
        $this->post('/logout')->assertRedirect('/login');

        // 3. Peminjam -> /dashboard
        $resPeminjam = $this->post('/login', [
            'username' => $peminjam->username,
            'password' => 'password',
        ]);
        $resPeminjam->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($peminjam);
        $this->get('/dashboard')->assertOk();
    }

    public function test_login_gagal_menampilkan_alert_error_mempertahankan_old_username_dan_memicu_animate_shake(): void
    {
        $user = $this->buatUser('petugas', 'ptg_fail');

        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'username' => $user->username,
            'password' => 'salah-total',
            'role_tab' => 'petugas',
        ]);

        $response->assertOk();
        $response->assertSee('Username atau password salah.');
        $response->assertSee('value="' . $user->username . '"', false);
        $response->assertSee('animate-shake', false);
        $response->assertSee('name="role_tab" value="petugas"', false);
        $this->assertGuest();
    }

    public function test_session_error_menampilkan_banner_alert_dan_animate_shake(): void
    {
        $response = $this->withSession(['error' => 'Sesi login berakhir, silakan masuk kembali.'])
            ->get('/login');

        $response->assertOk();
        $response->assertSee('Sesi login berakhir, silakan masuk kembali.');
        $response->assertSee('animate-shake', false);
    }
}
