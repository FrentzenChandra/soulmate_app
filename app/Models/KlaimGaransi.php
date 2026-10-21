<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KlaimGaransi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'klaim_garansis';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'no_klaim',
        'tanggal_masuk',
        'customer_id',
        'customer_nama',
        'customer_kontak',
        'barang',
        'keluhan',
        'master_distributor_id',
        'distributor_nama',
        'tanggal_kirim',
        'tindakan',
        'tanggal_selesai_klaim',
        'tanggal_barang_kembali',
        'penerima_pegawai_id',
        'penerima_nama',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_kirim' => 'date',
        'tanggal_selesai_klaim' => 'date',
        'tanggal_barang_kembali' => 'date',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function distributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'master_distributor_id')->withTrashed();
    }

    public function penerima()
    {
        return $this->belongsTo(Pegawai::class, 'penerima_pegawai_id')->withTrashed();
    }

    /**
     * Generate Nomor Klaim Garansi harian unik: KG-YYMMDD-00001
     */
    public static function generateNoKlaim(int $cabangId, ?string $tanggal = null): string
    {
        $date = $tanggal ? Carbon::parse($tanggal) : Carbon::today();
        $dateCode = $date->format('ymd');
        $prefix = "KG-{$dateCode}-";

        $lastKlaim = static::where('cabang_id', $cabangId)
            ->where('no_klaim', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextSeq = 1;
        if ($lastKlaim && preg_match('/-(\d+)$/', $lastKlaim->no_klaim, $m)) {
            $nextSeq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
    }
}

