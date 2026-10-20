<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FakturPajak extends Model
{
    use HasFactory;

    protected $table = 'faktur_pajaks';

    protected $fillable = [
        'no_faktur',
        'tanggal',
        'cabang_id',
        'user_id',
        'is_selesai',
        'selesai_at',
        'selesai_by',
        'master_orang_pajak_id',
        'master_via_pajak_id',
        'master_kode_pajak_id',
        'master_pph_id',
        'nama_pajak_snapshot',
        'barang_snapshot',
        'via_pajak_snapshot',
        'kode_pajak_snapshot',
        'bruto',
        'dpp',
        'dpp_lain',
        'ppn_persen',
        'ppn_nominal',
        'jenis_pph_snapshot',
        'pph_persen',
        'pph_nominal',
        'netto',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_selesai' => 'boolean',
        'selesai_at' => 'datetime',
        'bruto' => 'decimal:2',
        'dpp' => 'decimal:2',
        'dpp_lain' => 'decimal:2',
        'ppn_persen' => 'decimal:2',
        'ppn_nominal' => 'decimal:2',
        'pph_persen' => 'decimal:2',
        'pph_nominal' => 'decimal:2',
        'netto' => 'decimal:2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function selesaiBy()
    {
        return $this->belongsTo(User::class, 'selesai_by')->withTrashed();
    }

    public function masterOrangPajak()
    {
        return $this->belongsTo(MasterOrangPajak::class, 'master_orang_pajak_id');
    }

    public function masterViaPajak()
    {
        return $this->belongsTo(MasterViaPajak::class, 'master_via_pajak_id');
    }

    public function masterKodePajak()
    {
        return $this->belongsTo(MasterKodePajak::class, 'master_kode_pajak_id');
    }

    public function masterPph()
    {
        return $this->belongsTo(MasterPph::class, 'master_pph_id');
    }

    /**
     * Generate Nomor Faktur Pajak harian unik: FP-YYMMDD-00001
     */
    public static function generateNoFaktur(int $cabangId, ?string $tanggal = null): string
    {
        $date = $tanggal ? Carbon::parse($tanggal) : Carbon::today();
        $dateCode = $date->format('ymd');
        $prefix = "FP-{$dateCode}-";

        $lastFaktur = static::where('cabang_id', $cabangId)
            ->where('no_faktur', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextSeq = 1;
        if ($lastFaktur && preg_match('/-(\d+)$/', $lastFaktur->no_faktur, $m)) {
            $nextSeq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
    }
}

