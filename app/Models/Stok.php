<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stoks';

    protected $fillable = [
        'produk_id',
        'cabang_id',
        'jumlah',
        'min_stok',
        'is_aktif',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'min_stok' => 'integer',
        'is_aktif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function ($stok) {
            \App\Livewire\Components\NotificationBell::clearNotificationCache();
        });

        static::deleted(function ($stok) {
            \App\Livewire\Components\NotificationBell::clearNotificationCache();
        });
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }
}
