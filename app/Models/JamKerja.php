<?php

namespace App\Models;

use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamKerja extends Model
{
    use HasFactory;

    protected $table = 'jam_kerjas';

    protected $fillable = [
        'cabang_id',
        'nama_shift',
        'jam_rentang_mulai',
        'jam_mulai',
        'jam_masuk',
        'toleransi_menit',
        'batas_kritis_menit',
        'jam_cutoff',
        'is_aktif',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'nama_shift' => ShiftType::class,
            'toleransi_menit' => 'integer',
            'batas_kritis_menit' => 'integer',
            'is_aktif' => 'boolean',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    /**
     * Dapatkan konfigurasi jam kerja aktif untuk shift dan cabang tertentu.
     * Mengambil override cabang jika ada, atau fallback ke global (cabang_id = null).
     */
    public static function getRuleFor(string|ShiftType $shift, ?int $cabangId = null): ?self
    {
        $shiftVal = $shift instanceof ShiftType ? $shift->value : $shift;

        if ($cabangId) {
            $cabangRule = self::where('cabang_id', $cabangId)
                ->where('nama_shift', $shiftVal)
                ->where('is_aktif', true)
                ->first();

            if ($cabangRule) {
                return $cabangRule;
            }
        }

        return self::whereNull('cabang_id')
            ->where('nama_shift', $shiftVal)
            ->where('is_aktif', true)
            ->first();
    }
}

