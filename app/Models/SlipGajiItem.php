<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlipGajiItem extends Model
{
    use HasFactory;

    protected $table = 'slip_gaji_items';

    protected $fillable = [
        'slip_gaji_id',
        'tipe',
        'kategori',
        'nama_item',
        'nominal',
        'urutan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'urutan' => 'integer',
    ];

    public function slipGaji(): BelongsTo
    {
        return $this->belongsTo(SlipGaji::class, 'slip_gaji_id');
    }
}

