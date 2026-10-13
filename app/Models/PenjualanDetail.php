<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_details';

    protected $fillable = [
        'penjualan_id',
        'tipe_item',
        'produk_id',
        'nama_item',
        'qty',
        'qty_retur',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'qty_retur' => 'integer',
        'harga' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function returs()
    {
        return $this->hasMany(PenjualanRetur::class, 'penjualan_detail_id');
    }
}
