<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Di PostgreSQL, rollback transaksi pada DatabaseTransactions tidak mereset SEQUENCE.
        // Sinkronkan ulang sequence setelah rollback agar test tidak melompatkan ID di database lokal.
        $this->beforeApplicationDestroyed(function () {
            $connection = $this->app->make('db')->connection();
            if ($connection->getDriverName() !== 'pgsql') {
                return;
            }

            $tables = [
                'users' => 'id_user',
                'kategori' => 'id_kategori',
                'alat' => 'id_alat',
                'peminjaman' => 'id_peminjaman',
                'detail_peminjaman' => 'id_detail',
                'pengembalian' => 'id_pengembalian',
                'log_aktivitas' => 'id_log',
            ];

            foreach ($tables as $table => $pk) {
                $connection->statement(
                    "SELECT setval(pg_get_serial_sequence('{$table}', '{$pk}'), COALESCE(MAX({$pk}), 1), MAX({$pk}) IS NOT NULL) FROM {$table}"
                );
            }
        });
    }
}
