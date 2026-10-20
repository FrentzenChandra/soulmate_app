<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterViaPajak extends Model
{
    use HasFactory;

    protected $table = 'master_via_pajaks';

    protected $fillable = [
        'nama',
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
        return $this->hasMany(FakturPajak::class, 'master_via_pajak_id');
    }
}

