<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetPegawai extends Model
{
    use HasFactory;

    protected $table = 'target_pegawais';

    protected $fillable = [
        'cabang_id',
        'pegawai_id',
        'tahun',
        'target_bulanan',
        'monthly_targets',
        'created_by',
    ];

    protected $casts = [
        'monthly_targets' => 'array',
        'tahun' => 'integer',
        'target_bulanan' => 'integer',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
