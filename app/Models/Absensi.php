<?php

namespace App\Models;

use App\Enums\ShiftType;
use App\Enums\StatusKehadiran;
use Database\Factories\AbsensiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    /** @use HasFactory<AbsensiFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'absensis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cabang_id',
        'user_id',
        'tanggal',
        'waktu_absen',
        'shift',
        'status_kehadiran',
        'menit_keterlambatan',
        'snapshot_jam_masuk',
        'snapshot_toleransi_menit',
        'ip_address',
        'device_id',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu_absen' => 'datetime',
            'shift' => ShiftType::class,
            'status_kehadiran' => StatusKehadiran::class,
            'menit_keterlambatan' => 'integer',
            'snapshot_toleransi_menit' => 'integer',
        ];
    }

    /**
     * Cabang tempat user/absensi dicatat.
     */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * User staf pemilik catatan absensi (Wajib withTrashed untuk integritas data historis).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}

