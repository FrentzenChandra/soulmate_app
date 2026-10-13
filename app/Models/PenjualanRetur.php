<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanRetur extends Model
{
    use HasFactory;

    protected $table = 'penjualan_returs';

    protected $fillable = [
        'penjualan_id',
        'penjualan_detail_id',
        'cabang_id',
        'produk_id',
        'user_id',
        'qty_retur',
        'stok_sebelum',
        'stok_sesudah',
        'alasan',
    ];

    protected $casts = [
        'qty_retur' => 'integer',
        'stok_sebelum' => 'integer',
        'stok_sesudah' => 'integer',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function detail()
    {
        return $this->belongsTo(PenjualanDetail::class, 'penjualan_detail_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}

