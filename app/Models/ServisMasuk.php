<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServisMasuk extends Model
{
    use HasFactory;

    protected $table = 'servis_masuks';

    protected $fillable = [
        'no_servis',
        'cabang_id',
        'customer_id',
        'teknisi_id',
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
        'catatan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'tanggal_ambil' => 'datetime',
        'estimasi_biaya' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(ServisDetail::class, 'servis_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(ServisStatusLog::class, 'servis_id');
    }

    public function penjualan()
    {
        return $this->hasOne(Penjualan::class, 'servis_id');
    }

    public function getNoServisFrontendAttribute(): string
    {
        $raw = (string) $this->no_servis;
        if (preg_match('/(?:INV-)?(SV)-[A-Za-z0-9_-]+?-(\d+-\d+)$/i', $raw, $matches)) {
            return $matches[1] . '-' . $matches[2];
        }

        return preg_replace('/^INV-([A-Za-z]+)-[A-Za-z0-9_-]+-(\d+-\d+)$/i', '$1-$2', $raw);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pengecekan' => 'Pengecekan / Diagnosa',
            'pengerjaan' => 'Sedang Dikerjakan',
            'menunggu_sparepart' => 'Menunggu Sparepart',
            'selesai' => 'Selesai (Siap Diambil)',
            'sudah_dibayar' => 'Sudah Diambil (Lunas)',
            'batal' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getKategoriPerangkatLabelAttribute(): string
    {
        return match (strtolower((string) $this->kategori_perangkat)) {
            'laptop' => 'Laptop / Notebook',
            'komputer' => 'PC Komputer / Desktop',
            'printer' => 'Printer / Scanner',
            'lainnya' => 'Perangkat Lainnya',
            default => ucfirst((string) $this->kategori_perangkat),
        };
    }
}

