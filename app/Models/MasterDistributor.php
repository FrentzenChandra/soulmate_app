<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDistributor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_distributors';

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

    public function klaimGaransis()
    {
        return $this->hasMany(KlaimGaransi::class, 'master_distributor_id');
    }
}

