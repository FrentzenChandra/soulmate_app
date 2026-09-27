<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Cabang extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('master_cabangs_active'));
        static::deleted(fn () => Cache::forget('master_cabangs_active'));
    }

    public static function getAllCached()
    {
        return Cache::remember('master_cabangs_active', 86400, fn () => static::where('status', 'aktif')->orderBy('nama_cabang')->get());
    }

    protected $table = 'cabangs';

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'no_telp',
        'no_wa',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function stoks()
    {
        return $this->hasMany(Stok::class);
    }

    public function servisMasuks()
    {
        return $this->hasMany(ServisMasuk::class);
    }

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
    }

    public function getNoWaFormattedAttribute(): string
    {
        return format_whatsapp($this->no_wa ?? '081995466255');
    }
}
