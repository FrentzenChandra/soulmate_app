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

    public function getBagianAttribute(): ?string
    {
        return $this->role;
    }

    public function setBagianAttribute(?string $value): void
    {
        $this->attributes['role'] = $value;
    }

    public function getBagianBadgeClassAttribute(): string
    {
        $val = strtolower($this->role ?? '');

        if (str_contains($val, 'laptop')) {
            return 'bg-blue-100 text-blue-700 border-blue-200';
        }
        if (str_contains($val, 'printer')) {
            return 'bg-amber-100 text-amber-800 border-amber-200';
        }
        if (str_contains($val, 'komputer') || str_contains($val, 'pc')) {
            return 'bg-purple-100 text-[#5E17EB] border-purple-200';
        }
        if (str_contains($val, 'bendahara')) {
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        }
        if (str_contains($val, 'pajak')) {
            return 'bg-rose-100 text-rose-800 border-rose-200';
        }
        if (str_contains($val, 'stok')) {
            return 'bg-cyan-100 text-cyan-800 border-cyan-200';
        }
        if (str_contains($val, 'konten')) {
            return 'bg-indigo-100 text-indigo-800 border-indigo-200';
        }
        if (str_contains($val, 'teknisi')) {
            return 'bg-teal-100 text-teal-800 border-teal-200';
        }
        if (str_contains($val, 'admin')) {
            return 'bg-blue-100 text-[#0052FF] border-blue-200';
        }

        return 'bg-gray-100 text-gray-800 border-gray-200';
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return $this->getBagianBadgeClassAttribute();
    }
}
