<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifGaji extends Model
{
    use HasFactory;

    protected $table = 'tarif_gajis';

    protected $fillable = [
        'jenis',
        'nominal',
        'keterangan',
        'updated_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    /**
     * Ambil nominal tarif berdasarkan jenis dengan fallback default.
     */
    public static function getNominal(string $jenis, float $default = 0): float
    {
        $tarif = static::where('jenis', $jenis)->first();

        return $tarif ? (float) $tarif->nominal : $default;
    }
}

