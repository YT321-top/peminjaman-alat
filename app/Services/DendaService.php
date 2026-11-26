<?php

namespace App\Services;

use Carbon\Carbon;

class DendaService
{
    public const BATAS_HARI_PINJAM = 3;
    public const TARIF_DENDA_PER_HARI = 2000;

    /**
     * Hitung estimasi keterlambatan dan saran nominal denda.
     *
     * @return array{hari_pinjam: int, hari_terlambat: int, estimasi_denda: int}
     */
    public static function hitungEstimasi(Carbon $tanggalPinjam, ?Carbon $tanggalKembali = null): array
    {
        $kembali = ($tanggalKembali ? $tanggalKembali->copy() : now())->startOfDay();
        $pinjam = $tanggalPinjam->copy()->startOfDay();

        $hariPinjam = $kembali->greaterThan($pinjam)
            ? (int) $pinjam->diffInDays($kembali)
            : 0;

        $hariTerlambat = max(0, $hariPinjam - self::BATAS_HARI_PINJAM);
        $estimasiDenda = $hariTerlambat * self::TARIF_DENDA_PER_HARI;

        return [
            'hari_pinjam' => $hariPinjam,
            'hari_terlambat' => $hariTerlambat,
            'estimasi_denda' => $estimasiDenda,
        ];
    }
}
