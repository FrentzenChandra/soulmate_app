<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPpn extends Model
{
    use HasFactory;

    protected $table = 'master_ppns';

    protected $fillable = [
        'nama',
        'persen',
        'is_aktif',
    ];

    protected $casts = [
        'persen' => 'decimal:2',
        'is_aktif' => 'boolean',
    ];

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'ppn_id');
    }
}
