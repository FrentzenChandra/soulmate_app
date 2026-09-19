<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKasKeluar extends Model
{
    use HasFactory;

    protected $table = 'kategori_kas_keluars';

    protected $fillable = [
        'nama',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function kasKeluars()
    {
        return $this->hasMany(KasKeluar::class, 'kategori_kas_keluar_id');
    }
}
