<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Str;

class LogAktivitasService
{
    public static function record(?int $idUser, string $aksi): void
    {
        if (!$idUser) {
            return;
        }

        LogAktivitas::create([
            'id_user' => $idUser,
            'aksi' => Str::limit(trim($aksi), 255, ''),
            'waktu' => now(),
        ]);
    }
}
