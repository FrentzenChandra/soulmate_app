<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPph extends Model
{
    use HasFactory;

    protected $table = 'master_pphs';

    protected $fillable = [
        'nama_pph',
        'persentase',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'persentase' => 'decimal:2',
        'is_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function fakturPajaks()
    {
        return $this->hasMany(FakturPajak::class, 'master_pph_id');
    }
}

