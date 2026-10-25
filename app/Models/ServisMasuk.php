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
            'antrean_masuk' => 'Antrean Masuk',
            'pengecekan' => 'Pengecekan',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'menunggu_sparepart' => 'Menunggu Sparepart',
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
            'antrean_masuk' => 'bg-rose-100 text-rose-800 border border-rose-300 font-black',
            'pengecekan' => 'bg-amber-100 text-amber-800 border border-amber-300 font-black',
            'menunggu_konfirmasi' => 'bg-pink-100 text-[#E6007A] border border-pink-300 font-black',
            'menunggu_sparepart' => 'bg-purple-100 text-[#5E17EB] border border-purple-300 font-black',
            'perbaikan', 'pengerjaan' => 'bg-blue-100 text-[#0052FF] border border-blue-300 font-black',
            'selesai' => 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-black',
            'sudah_diambil', 'sudah_dibayar' => 'bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold',
            'batal' => 'bg-rose-100 text-rose-800 border border-rose-300 font-black',
            default => 'bg-gray-100 text-gray-800 border border-gray-300 font-black',
        };
    }

    public function getStatusBorderClassAttribute(): string
    {
        return match ($this->status) {
            'antrean_masuk' => 'border-l-4 border-rose-500',
            'pengecekan' => 'border-l-4 border-amber-500',
            'menunggu_konfirmasi' => 'border-l-4 border-pink-500',
            'menunggu_sparepart' => 'border-l-4 border-purple-500',
            'perbaikan', 'pengerjaan' => 'border-l-4 border-blue-500',
            'selesai' => 'border-l-4 border-emerald-500',
            'sudah_diambil', 'sudah_dibayar' => 'border-l-4 border-indigo-500',
            'batal' => 'border-l-4 border-rose-600',
            default => 'border-l-4 border-gray-400',
        };
    }

    public function getKategoriPerangkatLabelAttribute(): string
    {
        return match (strtolower($this->kategori_perangkat)) {
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'komputer', 'pc' => 'Komputer',
            default => ucfirst($this->kategori_perangkat ?: 'Komputer'),
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
