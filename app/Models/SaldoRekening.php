<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoRekening extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function sumber(): BelongsTo
    {
        return $this->belongsTo(SumberSaldo::class, 'sumber_saldo_id');
    }
}
