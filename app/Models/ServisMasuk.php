<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServisMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_servis',
        'cabang_id',
        'customer_id',
        'teknisi_id',
        'kasir_id',
        'created_by',
        'kategori_perangkat',
        'tipe_unit',
        'serial_number',
        'kelengkapan',
        'keluhan',
        'kondisi_barang',
        'status',
        'is_real',
        'is_cetak_nota',
        'tanggal_masuk',
        'tanggal_selesai',
        'tanggal_ambil',
        'estimasi_biaya',
        'total_biaya',
        'metode_pembayaran',
        'bayar',
        'kembalian',
        'garansi_hari',
        'garansi_sampai',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'is_real' => 'boolean',
            'is_cetak_nota' => 'boolean',
            'tanggal_masuk' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'tanggal_ambil' => 'datetime',
            'garansi_sampai' => 'date',
            'estimasi_biaya' => 'decimal:2',
            'total_biaya' => 'decimal:2',
            'bayar' => 'decimal:2',
            'kembalian' => 'decimal:2',
            'garansi_hari' => 'integer',
        ];
    }

    public function scopeReal($query)
    {
        return $query->where('is_real', true);
    }

    public function scopeNonReal($query)
    {
        return $query->where('is_real', false);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id')->withTrashed();
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id')->withTrashed();
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function details(): HasMany
    {
        return $this->hasMany(ServisDetail::class, 'servis_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ServisStatusLog::class, 'servis_id')->orderBy('id', 'desc');
    }

    public function getNoServisFrontendAttribute(): string
    {
        $parts = explode('-', $this->no_servis);
        if (count($parts) >= 5 && $parts[0] === 'INV' && $parts[1] === 'SV') {
            return "SV-{$parts[3]}-{$parts[4]}";
        }
        return $this->no_servis;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pengecekan' => 'Pengecekan',
            'perbaikan', 'pengerjaan' => 'Perbaikan',
            'selesai' => 'Selesai',
            'sudah_diambil', 'sudah_dibayar' => 'Sudah Diambil',
            'batal' => 'Batal',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pengecekan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'perbaikan', 'pengerjaan' => 'bg-blue-50 text-blue-700 border-blue-200',
            'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'sudah_diambil', 'sudah_dibayar' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    public function getKategoriPerangkatLabelAttribute(): string
    {
        return match (strtolower($this->kategori_perangkat)) {
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'komputer', 'pc' => 'Komputer',
            default => ucfirst($this->kategori_perangkat ?: 'Lainnya'),
        };
    }

    public function getTotalJasaAttribute(): float
    {
        return (float) $this->details->filter(function ($d) {
            if ($d->jenis) return $d->jenis === 'jasa';
            if ($d->masterServis) return $d->masterServis->jenis === 'jasa';
            return is_null($d->produk_id);
        })->sum('subtotal');
    }

    public function getTotalSparepartAttribute(): float
    {
        return (float) $this->details->filter(function ($d) {
            if ($d->jenis) return $d->jenis === 'sparepart';
            if ($d->masterServis) return $d->masterServis->jenis === 'sparepart';
            return ! is_null($d->produk_id);
        })->sum('subtotal');
    }

    public function getIsBergaransiAttribute(): bool
    {
        if ($this->garansi_hari <= 0 || ! $this->garansi_sampai) {
            return false;
        }
        return Carbon::today()->lte($this->garansi_sampai);
    }
}
