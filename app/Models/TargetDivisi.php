<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetDivisi extends Model
{
    use HasFactory;

    protected $table = 'target_divisis';

    protected $fillable = [
        'cabang_id',
        'tahun',
        'divisi',
        'target_bulanan',
        'monthly_targets',
        'monthly_realisasi',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'target_bulanan' => 'integer',
        'monthly_targets' => 'array',
        'monthly_realisasi' => 'array',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function getDivisiLabelAttribute(): string
    {
        return match (strtolower($this->divisi)) {
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'komputer' => 'Komputer',
            'admin' => 'Admin',
            default => ucfirst($this->divisi),
        };
    }
}
