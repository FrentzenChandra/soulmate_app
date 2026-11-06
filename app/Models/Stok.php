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

    /**
     * Sinkronkan status ketersediaan produk setelah stok berubah.
     * Default satu arah (hanya menonaktifkan). Reaktivasi hanya untuk retur/pembatalan.
     */
    public static function syncAvailability(int $produkId, bool $allowReactivate = false): void
    {
        self::where('produk_id', $produkId)->where('jumlah', '<=', 0)->update(['is_aktif' => false]);

        $punyaBaris = self::where('produk_id', $produkId)->exists();
        $adaKosong = self::where('produk_id', $produkId)->where('jumlah', '<=', 0)->exists();

        if ($allowReactivate) {
            self::where('produk_id', $produkId)->where('jumlah', '>', 0)->update(['is_aktif' => true]);

            if ($punyaBaris && ! $adaKosong) {
                Produk::whereKey($produkId)->update(['ready_marketplace' => true]);
            }
        }

        if (! $punyaBaris || $adaKosong) {
            Produk::whereKey($produkId)->where('ready_marketplace', true)->update(['ready_marketplace' => false]);
        }
    }
}
