<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterServisStok extends Model
{
    use HasFactory;

    protected $table = 'master_servis_stoks';

    protected $fillable = [
        'master_servis_id',
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

    public function masterServis(): BelongsTo
    {
        return $this->belongsTo(MasterServis::class, 'master_servis_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
