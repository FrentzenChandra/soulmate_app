<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CekKasLog extends Model
{
    use HasFactory;

    protected $table = 'cek_kas_logs';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'tanggal',
        'waktu_cek',
        'kas_sistem',
        'kas_fisik',
        'selisih',
        'status_selisih',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu_cek' => 'datetime',
            'kas_sistem' => 'decimal:2',
            'kas_fisik' => 'decimal:2',
            'selisih' => 'decimal:2',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
