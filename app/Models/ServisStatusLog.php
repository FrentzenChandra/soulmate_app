<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServisStatusLog extends Model
{
    use HasFactory;

    protected $table = 'servis_status_logs';

    protected $fillable = [
        'servis_id',
        'user_id',
        'status',
        'tanggal',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function servisMasuk()
    {
        return $this->belongsTo(ServisMasuk::class, 'servis_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
