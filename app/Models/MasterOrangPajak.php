<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOrangPajak extends Model
{
    use HasFactory;

    protected $table = 'master_orang_pajaks';

    protected $fillable = [
        'nama',
        'npwp',
        'kontak',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function fakturPajaks()
    {
        return $this->hasMany(FakturPajak::class, 'master_orang_pajak_id');
    }
}

