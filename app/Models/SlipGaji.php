<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlipGaji extends Model
{
    use HasFactory;

    protected $table = 'slip_gajis';

    protected $fillable = [
        'no_slip',
        'cabang_id',
        'user_id',
        'periode_bulan',
        'periode_tahun',
        'status',
        'source_slip_id',

        // Snapshot
        'nama_staf_snapshot',
        'bagian_snapshot',
        'tanggal_lahir_snapshot',
        'tanggal_mulai_bekerja_snapshot',
        'masa_kerja_snapshot',
        'usia_snapshot',

        // Finansial
        'gaji_pokok',
        'uang_makan_hari',
        'uang_makan_per_hari',
        'uang_makan_total',
        'uang_transport_hari',
        'uang_transport_per_hari',
        'uang_transport_total',
        'tunjangan_nama',
        'tunjangan_nominal',
        'bonus_nominal',
        'lembur_performa_nominal',
        'servisan_selesai_nominal',
        'total_bruto',
        'total_pengurangan',
        'total_diterima',

        // Audit Nilai Tetap
        'diubah_nilai_tetap_by',
        'diubah_nilai_tetap_at',

        // Audit Trail
        'created_by',
        'finalized_by',
        'finalized_at',
        'alasan_buka_kembali',
        'reopened_by',
        'reopened_at',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'tanggal_lahir_snapshot' => 'date',
        'tanggal_mulai_bekerja_snapshot' => 'date',
        'gaji_pokok' => 'decimal:2',
        'uang_makan_hari' => 'integer',
        'uang_makan_per_hari' => 'decimal:2',
        'uang_makan_total' => 'decimal:2',
        'uang_transport_hari' => 'integer',
        'uang_transport_per_hari' => 'decimal:2',
        'uang_transport_total' => 'decimal:2',
        'tunjangan_nominal' => 'decimal:2',
        'bonus_nominal' => 'decimal:2',
        'lembur_performa_nominal' => 'decimal:2',
        'servisan_selesai_nominal' => 'decimal:2',
        'total_bruto' => 'decimal:2',
        'total_pengurangan' => 'decimal:2',
        'total_diterima' => 'decimal:2',
        'diubah_nilai_tetap_at' => 'datetime',
        'finalized_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SlipGajiItem::class, 'slip_gaji_id')->orderBy('urutan');
    }

    public function sourceSlip(): BelongsTo
    {
        return $this->belongsTo(SlipGaji::class, 'source_slip_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by')->withTrashed();
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuka_kembali_by')->withTrashed();
    }

    public function dibukaKembaliBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuka_kembali_by')->withTrashed();
    }

    public function diubahNilaiTetapBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_nilai_tetap_by')->withTrashed();
    }

    public function getPeriodeTextAttribute(): string
    {
        $date = Carbon::createFromDate($this->periode_tahun, $this->periode_bulan, 1);

        return $date->locale('id')->translatedFormat('F Y');
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}

