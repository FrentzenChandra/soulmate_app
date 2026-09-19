<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    use HasFactory;

    protected $table = 'metode_pembayarans';

    protected $fillable = [
        'nama',
        'kategori',
        'nomor_rekening',
        'atas_nama',
        'urutan',
        'is_aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true)->orderBy('urutan', 'asc')->orderBy('nama', 'asc');
    }
}
