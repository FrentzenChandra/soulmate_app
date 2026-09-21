<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KategoriProduk extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('master_kategoris_all'));
        static::deleted(fn () => Cache::forget('master_kategoris_all'));
    }

    public static function getAllCached()
    {
        return Cache::remember('master_kategoris_all', 86400, fn () => static::orderBy('nama_kategori')->get());
    }

    protected $table = 'kategori_produks';

    protected $fillable = [
        'nama_kategori',
        'slug',
    ];

    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
