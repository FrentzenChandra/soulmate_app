<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KenaikanGaji extends Model
{
    use HasFactory;

    protected $table = 'kenaikan_gajis';

    protected $fillable = [
        'user_id',
        'cabang_id',
        'milestone_bulan',
        'jatuh_tempo',
        'nominal',
        'status',
        'slip_gaji_id',
        'ditangani_by',
        'ditangani_at',
    ];

    protected $casts = [
        'milestone_bulan' => 'integer',
        'jatuh_tempo' => 'date',
        'nominal' => 'decimal:2',
        'ditangani_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function slipGaji(): BelongsTo
    {
        return $this->belongsTo(SlipGaji::class, 'slip_gaji_id');
    }

    public function ditanganiBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_by')->withTrashed();
    }
}

