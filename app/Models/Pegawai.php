<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';

    protected $fillable = [
        'cabang_id',
        'nama',
        'role',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function transaksiFulls(): HasMany
    {
        return $this->hasMany(TransaksiFull::class, 'pegawai_id');
    }

    public function targetPegawais(): HasMany
    {
        return $this->hasMany(TargetPegawai::class, 'pegawai_id');
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match (strtolower($this->role)) {
            'superadmin' => 'bg-purple-100 text-[#5E17EB] border-purple-200',
            'admin' => 'bg-blue-100 text-[#0052FF] border-blue-200',
            'teknisi' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'bendahara' => 'bg-amber-100 text-amber-800 border-amber-200',
            'kasir' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
