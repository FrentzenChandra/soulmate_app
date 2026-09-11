<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomorUrutTransaksi extends Model
{
    use HasFactory;

    protected $table = 'nomor_urut_transaksis';

    protected $fillable = [
        'cabang_id',
        'tipe',
        'tahun',
        'bulan',
        'last_number',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'last_number' => 'integer',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }
}
