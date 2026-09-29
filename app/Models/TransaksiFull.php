<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiFull extends Model
{
    use HasFactory;

    protected $table = 'transaksi_fulls';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'customer_id',
        'nama_pelanggan',
        'no_hp',
        'no_transaksi',
        'tipe_transaksi',
        'kategori_perangkat',
        'tanggal',
        'tanggal_lunas',
        'metode_pembayaran_id',
        'metode_pembayaran',
        'ppn_id',
        'ppn_persen',
        'ppn_nominal',
        'total_modal',
        'total_jual',
        'total_laba',
        'grand_total',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'tanggal_lunas' => 'datetime',
        'ppn_persen' => 'decimal:2',
        'ppn_nominal' => 'decimal:2',
        'total_modal' => 'decimal:2',
        'total_jual' => 'decimal:2',
        'total_laba' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function metodePembayaranRef(): BelongsTo
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }

    public function ppn(): BelongsTo
    {
        return $this->belongsTo(MasterPpn::class, 'ppn_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransaksiFullDetail::class, 'transaksi_full_id');
    }

    /**
     * Obfuscated Transaction Number for frontend display:
     * e.g. "INV-PJ-CB-PKP-260922-00001" -> "PJ-260922-00001"
     * e.g. "INV-SV-CB-PKP-260922-00001" -> "SV-260922-00001"
     */
    public function getNoTransaksiFrontendAttribute(): string
    {
        $raw = (string) $this->no_transaksi;

        if (preg_match('/(?:INV-)?(PJ|SV)-[A-Za-z0-9_-]+?-(\d+-\d+)$/i', $raw, $matches)) {
            return strtoupper($matches[1]) . '-' . $matches[2];
        }

        if (preg_match('/^INV-([A-Za-z]+)-[A-Za-z0-9_-]+-(\d+-\d+)$/i', $raw, $matches)) {
            return strtoupper($matches[1]) . '-' . $matches[2];
        }

        return str_replace('INV-', '', $raw);
    }

    public function getStatusLabelAttribute(): string
    {
        return match (strtolower(str_replace(' ', '_', $this->status))) {
            'lunas' => 'Lunas',
            'bon' => 'Bon',
            'belum_lunas' => 'Belum Lunas',
            'batal' => 'Batal',
            default => ucfirst($this->status),
        };
    }

    public function getKategoriPerangkatLabelAttribute(): ?string
    {
        if (! $this->kategori_perangkat) {
            return null;
        }

        return match (strtolower($this->kategori_perangkat)) {
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'komputer', 'pc' => 'Komputer',
            default => ucfirst($this->kategori_perangkat ?: 'Lainnya'),
        };
    }
}
