<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabangs';

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'no_telp',
        'no_wa',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function stoks()
    {
        return $this->hasMany(Stok::class);
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
