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
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
            'pengerjaan' => 'Pengerjaan',
            'menunggu_sparepart' => 'Menunggu Sparepart',
            'selesai' => 'Siap Diambil',
            'sudah_dibayar' => 'Sudah Diambil (Lunas)',
            'batal' => 'Batal',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pengecekan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'pengerjaan' => 'bg-blue-50 text-blue-700 border-blue-200',
            'menunggu_sparepart' => 'bg-purple-50 text-purple-700 border-purple-200',
            'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'sudah_dibayar' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    public function getKategoriPerangkatLabelAttribute(): string
    {
        return match (strtolower($this->kategori_perangkat)) {
            'laptop' => 'Laptop / Notebook',
            'printer' => 'Printer',
            'komputer', 'pc' => 'PC Desktop',
            default => ucfirst($this->kategori_perangkat ?: 'Lainnya'),
        };
    }

    public function getTotalJasaAttribute(): float
    {
        return (float) $this->details->whereNull('produk_id')->sum('subtotal');
    }

    public function getTotalSparepartAttribute(): float
    {
        return (float) $this->details->whereNotNull('produk_id')->sum('subtotal');
    }

    public function getIsBergaransiAttribute(): bool
    {
        if ($this->garansi_hari <= 0 || ! $this->garansi_sampai) {
            return false;
        }
        return Carbon::today()->lte($this->garansi_sampai);
    }
}
