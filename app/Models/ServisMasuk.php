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
}
