<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiFullDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_full_details';

    protected $fillable = [
        'transaksi_full_id',
        'produk_id',
        'nama_item',
        'qty',
        'harga_modal',
        'harga_jual',
        'subtotal_modal',
        'subtotal_jual',
        'laba',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_modal' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'subtotal_modal' => 'decimal:2',
        'subtotal_jual' => 'decimal:2',
        'laba' => 'decimal:2',
    ];

    public function transaksiFull(): BelongsTo
    {
        return $this->belongsTo(TransaksiFull::class, 'transaksi_full_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
