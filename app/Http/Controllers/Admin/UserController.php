<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LogAktivitasService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $allowedRoles = ['semua', 'admin', 'petugas', 'peminjam'];
        $role = (string) $request->query('role', 'semua');

        if (!in_array($role, $allowedRoles, true)) {
            $role = 'semua';
        }

        $q = trim((string) $request->query('q', ''));

        $rawCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $counts = [
            'semua' => (int) $rawCounts->sum(),
            'admin' => (int) ($rawCounts['admin'] ?? 0),
            'petugas' => (int) ($rawCounts['petugas'] ?? 0),
            'peminjam' => (int) ($rawCounts['peminjam'] ?? 0),
        ];

        $query = User::withCount('peminjaman')
            ->orderBy('id_user');

        if ($role !== 'semua') {
            $query->where('role', $role);
        }

        if ($q !== '') {
            $lowerQ = mb_strtolower($q);
            $query->whereRaw('LOWER(username) LIKE ?', ["%{$lowerQ}%"]);
        }

        $users = $query->paginate(12)->withQueryString();

        return view('admin.users.index', compact('users', 'role', 'q', 'counts'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', Rule::unique('users', 'username')],
            'role' => ['required', Rule::in(['admin', 'petugas', 'peminjam'])],
            'password' => ['required', 'string', 'min:6', 'max:100'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username sudah terdaftar. Gunakan username lain.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'password.required' => 'Password wajib diisi saat menambah user.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'username' => trim($validated['username']),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        LogAktivitasService::record(
            Auth::id(),
            "Menambah user baru {$user->username} (role: {$user->role})"
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->username} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id_user, 'id_user'),
            ],
            'role' => ['required', Rule::in(['admin', 'petugas', 'peminjam'])],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username sudah digunakan user lain.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        if ($user->id_user === Auth::id() && $validated['role'] !== 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat mengubah role akun sendiri keluar dari Admin.');
        }

        $oldUsername = $user->username;
        $user->username = trim($validated['username']);
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        LogAktivitasService::record(
            Auth::id(),
            "Memperbarui data user {$oldUsername} menjadi {$user->username} (role: {$user->role})"
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data user {$user->username} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id_user === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun milik sendiri yang sedang aktif.');
        }

        if ($user->peminjaman()->exists()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "User {$user->username} tidak dapat dihapus karena masih memiliki data peminjaman.");
        }

        $username = $user->username;
        $role = $user->role;

        try {
            $user->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "User {$username} tidak dapat dihapus karena masih terikat dengan data transaksi pada database.");
        }

        LogAktivitasService::record(
            Auth::id(),
            "Menghapus user {$username} (role: {$role})"
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$username} berhasil dihapus.");
    }
}
