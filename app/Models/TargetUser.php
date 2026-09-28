<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetUser extends Model
{
    use HasFactory;

    protected $table = 'target_users';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'tahun',
        'target_bulanan',
        'monthly_targets',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'target_bulanan' => 'integer',
        'monthly_targets' => 'array',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
