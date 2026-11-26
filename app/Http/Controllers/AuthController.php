<?php

namespace App\Http\Controllers;

use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            LogAktivitasService::record($user->id_user, 'Login ke sistem');

            $defaultRedirect = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'petugas' => route('petugas.peminjaman'),
                default => route('dashboard'),
            };

            return redirect()->intended($defaultRedirect);
        }

        return back()
            ->withErrors([
                'username' => 'Username atau password salah.',
            ])
            ->onlyInput('username', 'role_tab');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        LogAktivitasService::record($userId, 'Logout dari sistem');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Anda berhasil keluar dari sistem.');
    }
}
