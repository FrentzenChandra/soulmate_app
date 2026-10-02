<?php

namespace App\Models;

use App\Events\TransaksiUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiFull extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (TransaksiFull $model) {
            try {
                event(new TransaksiUpdated(
                    $model->id,
                    (string) $model->status,
                    (string) $model->no_transaksi,
                    $model->cabang_id,
                    'saved',
                    $model->toTabulatorRow()
                ));
            } catch (\Throwable $e) {
                // Ignore broadcast error
            }
        });

        static::deleted(function (TransaksiFull $model) {
            try {
                event(new TransaksiUpdated(
                    $model->id,
                    (string) $model->status,
                    (string) $model->no_transaksi,
                    $model->cabang_id,
                    'deleted'
                ));
            } catch (\Throwable $e) {
                // Ignore broadcast error
            }
        });
    }

    protected $table = 'transaksi_fulls';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'pegawai_id',
        'customer_id',
        'nama_pelanggan',
        'no_hp',
        'no_transaksi',
        'tipe_transaksi',
        'kategori_perangkat',
        'tanggal',
        'tanggal_lunas',
        'pelunas_user_id',
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

    public function userInput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function pelunas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelunas_user_id')->withTrashed();
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
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

    /**
     * Map model instance into Tabulator flat row payload
     */
    public function toTabulatorRow(): array
    {
        $this->loadMissing(['details', 'userInput', 'pelunas', 'pegawai']);

        return [
            'id' => $this->id,
            'no_transaksi' => $this->no_transaksi_frontend,
            'tipe_transaksi' => $this->tipe_transaksi,
            'waktu' => $this->tanggal ? $this->tanggal->format('d/m/Y H:i') : '-',
            'waktu_epoch' => $this->tanggal ? $this->tanggal->timestamp : 0,
            'jam' => $this->tanggal ? $this->tanggal->format('H:i') : '-',
            'tanggal_group' => $this->tanggal ? $this->tanggal->translatedFormat('l, d F Y') : '-',
            'petugas' => $this->pegawai?->nama ?? 'Tanpa Pegawai',
            'user_input' => $this->userInput?->name ?? 'System',
            'pelunas_nama' => $this->pelunas?->name ?? ($this->status === 'Lunas' ? ($this->userInput?->name ?? 'System') : '-'),
            'tanggal_lunas' => $this->tanggal_lunas ? $this->tanggal_lunas->format('d/m/Y H:i') : '-',
            'jam_lunas' => $this->tanggal_lunas ? $this->tanggal_lunas->format('H:i') : '-',
            'pelanggan' => $this->nama_pelanggan,
            'no_hp' => $this->no_hp ?: '-',
            'items_list' => $this->details->map(fn($d) => [
                'qty' => (int) $d->qty,
                'nama' => $d->nama_item,
                'harga_modal' => (float) $d->harga_modal,
                'harga_jual' => (float) $d->harga_jual,
                'subtotal_modal' => (float) ($d->subtotal_modal ?: ($d->harga_modal * $d->qty)),
                'subtotal_jual' => (float) ($d->subtotal_jual ?: ($d->harga_jual * $d->qty)),
                'laba' => (float) ($d->laba ?: (($d->harga_jual * $d->qty) - ($d->harga_modal * $d->qty))),
                'has_ppn' => ($d->ppn_persen > 0 || (float) $d->ppn_nominal > 0),
                'ppn_persen' => (float) $d->ppn_persen,
                'ppn_nominal' => (float) $d->ppn_nominal,
            ])->values()->all(),
            'rincian_item' => $this->details->map(fn($d) => $d->qty . 'x ' . $d->nama_item)->implode(', ') ?: 'Tanpa item',
            'metode_pembayaran_id' => $this->metode_pembayaran_id,
            'metode_pembayaran' => $this->metode_pembayaran ?: '-',
            'total_modal' => (float) $this->total_modal,
            'total_jual' => (float) $this->total_jual,
            'grand_total' => (float) $this->grand_total,
            'total_laba' => (float) $this->total_laba,
            'has_ppn' => ($this->ppn_persen > 0 || (float) $this->ppn_nominal > 0),
            'ppn_persen' => (float) $this->ppn_persen,
            'ppn_nominal' => (float) $this->ppn_nominal,
            'status' => $this->status,
            'keterangan' => $this->catatan ?: '-',
        ];
    }
}
