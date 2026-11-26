<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    protected $rememberTokenName = '';

    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_user', 'id_user');
    }

    public function peminjamanDisetujui(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_petugas', 'id_user');
    }

    public function pengembalianDiterima(): HasMany
    {
        return $this->hasMany(Pengembalian::class, 'id_petugas', 'id_user');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'id_user', 'id_user');
    }
}
