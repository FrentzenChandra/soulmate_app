<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterServis extends Model
{
    use HasFactory;

    protected $table = 'master_servis';

    protected $fillable = [
        'kode_item',
        'nama_item',
        'jenis',
        'kategori_perangkat',
        'harga_modal',
        'harga_jual',
        'garansi_default_hari',
        'keterangan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_modal' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'garansi_default_hari' => 'integer',
            'is_aktif' => 'boolean',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(ServisDetail::class, 'master_servis_id');
    }

    public function stoks(): HasMany
    {
        return $this->hasMany(MasterServisStok::class, 'master_servis_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeJasa($query)
    {
        return $query->where('jenis', 'jasa');
    }

    public function scopeSparepart($query)
    {
        return $query->where('jenis', 'sparepart');
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'jasa' => 'Jasa Servis',
            'sparepart' => 'Sparepart',
            default => ucfirst($this->jenis),
        };
    }

    public function getKategoriPerangkatLabelAttribute(): string
    {
        return match ($this->kategori_perangkat) {
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'komputer' => 'Komputer',
            'semua' => 'Semua Perangkat',
            default => ucfirst($this->kategori_perangkat ?: 'Lainnya'),
        };
    }
}
