<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'cabang_id',
        'nama',
        'no_hp',
        'alamat',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function servisMasuks()
    {
        return $this->hasMany(ServisMasuk::class);
    }

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
    }
}
