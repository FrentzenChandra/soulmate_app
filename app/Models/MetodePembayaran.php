<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MetodePembayaran extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('master_metode_pembayarans_active'));
        static::deleted(fn () => Cache::forget('master_metode_pembayarans_active'));
    }

    public static function getAktifCached()
    {
        return Cache::remember('master_metode_pembayarans_active', 86400, fn () => static::aktif()->get());
    }

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
