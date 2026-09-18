<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServisDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'servis_id',
        'master_servis_id',
        'produk_id',
        'nama_item',
        'jenis',
        'qty',
        'harga',
        'subtotal',
        'garansi_hari',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'garansi_hari' => 'integer',
        ];
    }

    public function servis(): BelongsTo
    {
        return $this->belongsTo(ServisMasuk::class, 'servis_id');
    }

    public function masterServis(): BelongsTo
    {
        return $this->belongsTo(MasterServis::class, 'master_servis_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function getGaransiLabelAttribute(): string
    {
        if ($this->garansi_hari <= 0) {
            return 'Non-Garansi';
        }
        return "{$this->garansi_hari} Hari";
    }
}
