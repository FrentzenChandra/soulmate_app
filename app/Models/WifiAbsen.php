<?php

namespace App\Models;

use App\Services\AbsensiService;
use Database\Factories\WifiAbsenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WifiAbsen extends Model
{
    /** @use HasFactory<WifiAbsenFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wifi_absens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cabang_id',
        'nama',
        'ip_address',
        'is_aktif',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Boot the model and clear active IP cache when modified.
     */
    protected static function booted(): void
    {
        static::saved(function (WifiAbsen $model) {
            if ($model->cabang_id) {
                AbsensiService::clearCache($model->cabang_id);
            }
        });

        static::deleted(function (WifiAbsen $model) {
            if ($model->cabang_id) {
                AbsensiService::clearCache($model->cabang_id);
            }
        });
    }

    /**
     * Cabang tempat WiFi ini didaftarkan.
     */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * Pengguna yang mendaftarkan data WiFi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}

