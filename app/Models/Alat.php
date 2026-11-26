<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'alat';

    protected $primaryKey = 'id_alat';

    protected $fillable = [
        'id_kategori',
        'nama_alat',
        'jumlah_tersedia',
    ];

    protected $casts = [
        'jumlah_tersedia' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_alat', 'id_alat');
    }
}
