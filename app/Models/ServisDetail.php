<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServisDetail extends Model
{
    use HasFactory;

    protected $table = 'servis_details';

    protected $fillable = [
        'servis_id',
        'produk_id',
        'nama_item',
        'qty',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function servisMasuk()
    {
        return $this->belongsTo(ServisMasuk::class, 'servis_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
