<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MasterPpn extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('master_ppn_active');
            Cache::forget('master_ppns_all_active');
        });
        static::deleted(function () {
            Cache::forget('master_ppn_active');
            Cache::forget('master_ppns_all_active');
        });
    }

    public static function getAktifCached()
    {
        return Cache::remember('master_ppn_active', 86400, fn () => static::where('is_aktif', true)->first());
    }

    public static function getAllAktifCached()
    {
        return Cache::remember('master_ppns_all_active', 86400, fn () => static::where('is_aktif', true)->orderBy('persen')->get());
    }

    protected $table = 'master_ppns';

    protected $fillable = [
        'nama',
        'persen',
        'is_aktif',
    ];

    protected $casts = [
        'persen' => 'decimal:2',
        'is_aktif' => 'boolean',
    ];

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'ppn_id');
    }
}
