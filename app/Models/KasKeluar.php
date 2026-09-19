<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasKeluar extends Model
{
    use HasFactory;

    protected $table = 'kas_keluars';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'sumber_dana_id',
        'kategori_kas_keluar_id',
        'tanggal',
        'keterangan',
        'nominal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'nominal' => 'decimal:2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sumberDana()
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKasKeluar::class, 'kategori_kas_keluar_id');
    }
}
