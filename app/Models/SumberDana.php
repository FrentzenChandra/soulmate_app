<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SumberDana extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('master_sumber_danas_active'));
        static::deleted(fn () => Cache::forget('master_sumber_danas_active'));
    }

    public static function getAktifCached()
    {
        return Cache::remember('master_sumber_danas_active', 86400, fn () => static::where('is_aktif', true)->orderBy('nama')->get());
    }

    protected $table = 'sumber_danas';

    protected $fillable = [
        'nama',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function kasKeluars()
    {
        return $this->hasMany(KasKeluar::class, 'sumber_dana_id');
    }
}
