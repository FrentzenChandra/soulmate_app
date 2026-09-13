<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualans';

    protected $fillable = [
        'no_transaksi',
        'cabang_id',
        'kasir_id',
        'customer_id',
        'servis_id',
        'tipe_transaksi',
        'tanggal',
        'subtotal',
        'diskon',
        'ppn_id',
        'ppn_persen',
        'ppn_nominal',
        'total',
        'jumlah_bayar',
        'kembalian',
        'metode_bayar',
        'status',
        'is_real',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'ppn_persen' => 'decimal:2',
        'ppn_nominal' => 'decimal:2',
        'total' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'is_real' => 'boolean',
    ];

    public function scopeReal($query)
    {
        return $query->where('is_real', true);
    }

    public function scopeNonReal($query)
    {
        return $query->where('is_real', false);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function servisMasuk()
    {
        return $this->belongsTo(ServisMasuk::class, 'servis_id');
    }

    public function ppn()
    {
        return $this->belongsTo(MasterPpn::class, 'ppn_id');
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }
}
