<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $allowedRoles = ['semua', 'admin', 'petugas', 'peminjam'];
        $role = (string) $request->query('role', 'semua');

        if (!in_array($role, $allowedRoles, true)) {
            $role = 'semua';
        }

        $userId = $request->query('id_user');
        if ($userId === null || $userId === '' || !ctype_digit((string) $userId)) {
            $userId = '';
        }

        $tanggalMulai = $this->parseOptionalDate($request->query('tanggal_mulai'));
        $tanggalSelesai = $this->parseOptionalDate($request->query('tanggal_selesai'));

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        $q = trim((string) $request->query('q', ''));

        $users = User::orderBy('username')->get();

        $query = LogAktivitas::with('user')
            ->orderByDesc('waktu')
            ->orderByDesc('id_log');

        if ($userId !== '') {
            $query->where('id_user', (int) $userId);
        }

        if ($role !== 'semua') {
            $query->whereHas('user', fn ($u) => $u->where('role', $role));
        }

        if ($tanggalMulai) {
            $query->whereDate('waktu', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('waktu', '<=', $tanggalSelesai);
        }

        if ($q !== '') {
            $lowerQ = mb_strtolower($q);
            $query->where(function ($sub) use ($lowerQ) {
                $sub->whereRaw('LOWER(aksi) LIKE ?', ["%{$lowerQ}%"])
                    ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(username) LIKE ?', ["%{$lowerQ}%"]));
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.log-aktivitas.index', compact(
            'logs',
            'users',
            'userId',
            'role',
            'tanggalMulai',
            'tanggalSelesai',
            'q'
        ));
    }

    private function parseOptionalDate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', trim($value));
            if ($parsed !== false && $parsed->format('Y-m-d') === trim($value)) {
                return $parsed->toDateString();
            }
        } catch (Throwable) {
            // Abaikan format tanggal tidak valid
        }

        return null;
    }
}
