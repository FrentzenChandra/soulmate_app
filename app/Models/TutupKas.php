<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutupKas extends Model
{
    use HasFactory;

    protected $table = 'tutup_kas';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'tanggal',
        'waktu_tutup',
        'modal_awal',
        'total_penjualan_cash',
        'total_servis_cash',
        'total_kas_keluar_cash',
        'kas_sistem',
        'kas_fisik',
        'selisih',
        'status_selisih',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_tutup' => 'datetime',
        'modal_awal' => 'decimal:2',
        'total_penjualan_cash' => 'decimal:2',
        'total_servis_cash' => 'decimal:2',
        'total_kas_keluar_cash' => 'decimal:2',
        'kas_sistem' => 'decimal:2',
        'kas_fisik' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
