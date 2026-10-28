<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\KenaikanGaji;
use App\Models\SlipGaji;
use App\Models\SlipGajiItem;
use App\Models\TarifGaji;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SlipGajiService
{
    /**
     * Role staf yang berhak menerima slip gaji (wajib absen).
     * Superadmin dan Owner tidak digaji lewat modul ini.
     */
    public const STAF_ROLES = [
        'Operasional',
        'Bendahara',
        'Teknisi',
        'Admin',
        'Magang',
    ];

    /**
     * Hitung total hari hadir staf di periode bulan dan tahun tertentu.
     * Absen yang terlambat tetap dihitung sebagai hadir.
     */
    public function hitungHariHadir(int $userId, int $bulan, int $tahun): int
    {
        $start = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $end = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        return (int) Absensi::where('user_id', $userId)
            ->whereBetween('tanggal', [$start, $end])
            ->count();
    }

    /**
     * Ambil slip status Final terakhir staf sebelum atau pada periode tertentu.
     */
    public function ambilSlipFinalTerakhir(int $userId, ?int $beforeYear = null, ?int $beforeMonth = null): ?SlipGaji
    {
        $query = SlipGaji::where('user_id', $userId)
            ->where('status', 'final');

        if ($beforeYear && $beforeMonth) {
            $query->where(function ($q) use ($beforeYear, $beforeMonth) {
                $q->where('periode_tahun', '<', $beforeYear)
                    ->orWhere(function ($sub) use ($beforeYear, $beforeMonth) {
                        $sub->where('periode_tahun', $beforeYear)
                            ->where('periode_bulan', '<', $beforeMonth);
                    });
            });
        }

        return $query->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->first();
    }

    /**
     * Kalkulasi total bruto, total pengurangan, dan total diterima secara presisi desimal/integer rupiah.
     */
    public function kalkulasiTotal(array $penerimaan, array $penguranganItems = []): array
    {
        $gajiPokok = round((float) ($penerimaan['gaji_pokok'] ?? 0), 2);
        $uangMakanHari = (int) ($penerimaan['uang_makan_hari'] ?? 0);
        $uangMakanPerHari = round((float) ($penerimaan['uang_makan_per_hari'] ?? 0), 2);
        $uangMakanTotal = round($uangMakanHari * $uangMakanPerHari, 2);

        $uangTransportHari = (int) ($penerimaan['uang_transport_hari'] ?? 0);
        $uangTransportPerHari = round((float) ($penerimaan['uang_transport_per_hari'] ?? 0), 2);
        $uangTransportTotal = round($uangTransportHari * $uangTransportPerHari, 2);

        $tunjanganNominal = round((float) ($penerimaan['tunjangan_nominal'] ?? 0), 2);
        $bonusNominal = round((float) ($penerimaan['bonus_nominal'] ?? 0), 2);
        $lemburPerformaNominal = round((float) ($penerimaan['lembur_performa_nominal'] ?? 0), 2);
        $servisanSelesaiNominal = round((float) ($penerimaan['servisan_selesai_nominal'] ?? 0), 2);

        $totalBruto = round(
            $gajiPokok +
            $uangMakanTotal +
            $uangTransportTotal +
            $tunjanganNominal +
            $bonusNominal +
            $lemburPerformaNominal +
            $servisanSelesaiNominal,
            2
        );

        $totalPengurangan = 0.00;
        foreach ($penguranganItems as $item) {
            $totalPengurangan += round((float) ($item['nominal'] ?? 0), 2);
        }
        $totalPengurangan = round($totalPengurangan, 2);

        $totalDiterima = round($totalBruto - $totalPengurangan, 2);

        return [
            'gaji_pokok' => $gajiPokok,
            'uang_makan_hari' => $uangMakanHari,
            'uang_makan_per_hari' => $uangMakanPerHari,
            'uang_makan_total' => $uangMakanTotal,
            'uang_transport_hari' => $uangTransportHari,
            'uang_transport_per_hari' => $uangTransportPerHari,
            'uang_transport_total' => $uangTransportTotal,
            'tunjangan_nominal' => $tunjanganNominal,
            'bonus_nominal' => $bonusNominal,
            'lembur_performa_nominal' => $lemburPerformaNominal,
            'servisan_selesai_nominal' => $servisanSelesaiNominal,
            'total_bruto' => $totalBruto,
            'total_pengurangan' => $totalPengurangan,
            'total_diterima' => $totalDiterima,
        ];
    }

    /**
     * Buat salinan slip gaji dari slip Final terakhir staf (atau buat baru jika belum pernah punya slip).
     * Nilai Gaji Pokok, Tunjangan, dan Per Hari disalin apa adanya tanpa membaca tarif_gajis.
     * Hasil selalu berstatus Draft.
     */
    public function salinDariBulanLalu(User $staf, int $targetBulan, int $targetTahun, User $actor): SlipGaji
    {
        $existing = SlipGaji::where('user_id', $staf->id)
            ->where('periode_bulan', $targetBulan)
            ->where('periode_tahun', $targetTahun)
            ->first();

        if ($existing) {
            return $existing;
        }

        $source = $this->ambilSlipFinalTerakhir($staf->id, $targetTahun, $targetBulan);
        $hariHadir = $this->hitungHariHadir($staf->id, $targetBulan, $targetTahun);

        if ($source) {
            $gajiPokok = (float) $source->gaji_pokok;
            $uangMakanPerHari = (float) $source->uang_makan_per_hari;
            $uangTransportPerHari = (float) $source->uang_transport_per_hari;
            $tunjanganNama = $source->tunjangan_nama ?: 'Tunjangan Kepala Teknisi Laptop';
            $tunjanganNominal = (float) $source->tunjangan_nominal;
            $sourceSlipId = $source->id;
        } else {
            // Jika belum pernah punya slip final sebelumnya, gunakan tarif default global
            $gajiPokok = 0.00;
            $uangMakanPerHari = TarifGaji::getNominal('uang_makan', 20000.00);
            $uangTransportPerHari = TarifGaji::getNominal('uang_transport', 20000.00);
            $tunjanganNama = 'Tunjangan Kepala Teknisi Laptop';
            $tunjanganNominal = 0.00;
            $sourceSlipId = null;
        }

        $targetDate = Carbon::createFromDate($targetTahun, $targetBulan, 1);
        $masaKerja = $this->formatMasaKerja($staf->tanggal_mulai_bekerja ? Carbon::parse($staf->tanggal_mulai_bekerja) : null, $targetDate);
        $usia = $this->formatUsia($staf->tanggal_lahir ? Carbon::parse($staf->tanggal_lahir) : null, $targetDate);

        $calc = $this->kalkulasiTotal([
            'gaji_pokok' => $gajiPokok,
            'uang_makan_hari' => $hariHadir,
            'uang_makan_per_hari' => $uangMakanPerHari,
            'uang_transport_hari' => $hariHadir,
            'uang_transport_per_hari' => $uangTransportPerHari,
            'tunjangan_nominal' => $tunjanganNominal,
            'bonus_nominal' => 0.00,
            'lembur_performa_nominal' => 0.00,
            'servisan_selesai_nominal' => 0.00,
        ], []);

        $cabangId = $staf->cabang_id ?? 1;
        $noSlip = $this->generateNoSlip($cabangId, $targetTahun, $targetBulan, $staf->id);

        return DB::transaction(function () use (
            $cabangId,
            $staf,
            $targetBulan,
            $targetTahun,
            $noSlip,
            $sourceSlipId,
            $tunjanganNama,
            $calc,
            $masaKerja,
            $usia,
            $actor,
            $source
        ) {
            $slip = SlipGaji::create([
                'no_slip' => $noSlip,
                'cabang_id' => $cabangId,
                'user_id' => $staf->id,
                'periode_bulan' => $targetBulan,
                'periode_tahun' => $targetTahun,
                'status' => 'draft',
                'source_slip_id' => $sourceSlipId,

                'nama_staf_snapshot' => $staf->name,
                'bagian_snapshot' => $staf->bagian ?: '-',
                'tanggal_lahir_snapshot' => $staf->tanggal_lahir,
                'tanggal_mulai_bekerja_snapshot' => $staf->tanggal_mulai_bekerja,
                'masa_kerja_snapshot' => $masaKerja,
                'usia_snapshot' => $usia,

                'gaji_pokok' => $calc['gaji_pokok'],
                'uang_makan_hari' => $calc['uang_makan_hari'],
                'uang_makan_per_hari' => $calc['uang_makan_per_hari'],
                'uang_makan_total' => $calc['uang_makan_total'],

                'uang_transport_hari' => $calc['uang_transport_hari'],
                'uang_transport_per_hari' => $calc['uang_transport_per_hari'],
                'uang_transport_total' => $calc['uang_transport_total'],

                'tunjangan_nama' => $tunjanganNama,
                'tunjangan_nominal' => $calc['tunjangan_nominal'],
                'bonus_nominal' => 0.00,
                'lembur_performa_nominal' => 0.00,
                'servisan_selesai_nominal' => 0.00,

                'total_bruto' => $calc['total_bruto'],
                'total_pengurangan' => 0.00,
                'total_diterima' => $calc['total_diterima'],

                'created_by' => $actor->id,
            ]);

            // Jika slip sumber memiliki potongan, salin baris pengurangan
            if ($source && $source->items()->count() > 0) {
                $totalPengurangan = 0.00;
                foreach ($source->items as $item) {
                    SlipGajiItem::create([
                        'slip_gaji_id' => $slip->id,
                        'tipe' => $item->tipe,
                        'kategori' => $item->kategori,
                        'nama_item' => $item->nama_item,
                        'nominal' => $item->nominal,
                        'urutan' => $item->urutan,
                    ]);
                    $totalPengurangan += (float) $item->nominal;
                }
                $slip->update([
                    'total_pengurangan' => $totalPengurangan,
                    'total_diterima' => round($slip->total_bruto - $totalPengurangan, 2),
                ]);
            }

            return $slip;
        });
    }

    /**
     * Batch cloning: Salin slip untuk semua staf aktif wajib absen di cabang tersebut.
     * Mengembalikan rekapitulasi data termasuk staf yang memiliki kenaikan berstatus menunggu.
     */
    public function salinSemuaDariBulanLalu(int $cabangId, int $targetBulan, int $targetTahun, User $actor): array
    {
        $stafs = User::role(self::STAF_ROLES)
            ->where('status', 'aktif')
            ->where('cabang_id', $cabangId)
            ->orderBy('name')
            ->get();

        $disalinCount = 0;
        $sudahAdaCount = 0;

        foreach ($stafs as $staf) {
            $exists = SlipGaji::where('user_id', $staf->id)
                ->where('periode_bulan', $targetBulan)
                ->where('periode_tahun', $targetTahun)
                ->exists();

            if ($exists) {
                $sudahAdaCount++;
                continue;
            }

            $this->salinDariBulanLalu($staf, $targetBulan, $targetTahun, $actor);
            $disalinCount++;
        }

        // Cek daftar staf yang memiliki kenaikan berstatus menunggu
        $kenaikanMenunggu = KenaikanGaji::where('cabang_id', $cabangId)
            ->where('status', 'menunggu')
            ->with('user')
            ->get();

        $daftarNamaKenaikan = $kenaikanMenunggu->pluck('user.name')->filter()->unique()->values()->toArray();

        return [
            'disalin_count' => $disalinCount,
            'sudah_ada_count' => $sudahAdaCount,
            'kenaikan_menunggu_count' => count($daftarNamaKenaikan),
            'daftar_nama_kenaikan' => $daftarNamaKenaikan,
        ];
    }

    /**
     * Evaluasi otomatis milestone kenaikan gaji 6 bulanan untuk staf di cabang.
     * Baseline: Milestone sebelum awal bulan berjalan diberi status 'dilewati'.
     */
    public function evaluasiMilestoneKenaikan(int $cabangId): Collection
    {
        $stafs = User::role(self::STAF_ROLES)
            ->where('status', 'aktif')
            ->where('cabang_id', $cabangId)
            ->whereNotNull('tanggal_mulai_bekerja')
            ->get();

        $nominalKenaikan = TarifGaji::getNominal('kenaikan_6_bulan', 500000.00);
        $awalBulanBerjalan = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();

        foreach ($stafs as $staf) {
            $tglMulai = Carbon::parse($staf->tanggal_mulai_bekerja);
            $diffInMonths = (int) $tglMulai->diffInMonths(Carbon::now('Asia/Jakarta'));

            if ($diffInMonths < 6) {
                continue;
            }

            // Loop milestone kelipatan 6
            for ($milestone = 6; $milestone <= $diffInMonths; $milestone += 6) {
                $jatuhTempo = $tglMulai->copy()->addMonths($milestone)->toDateString();

                $exists = KenaikanGaji::where('user_id', $staf->id)
                    ->where('milestone_bulan', $milestone)
                    ->exists();

                if (! $exists) {
                    // Baseline: Jika jatuh tempo sebelum awal bulan berjalan, buat 'dilewati'
                    $status = ($jatuhTempo < $awalBulanBerjalan) ? 'dilewati' : 'menunggu';

                    KenaikanGaji::create([
                        'user_id' => $staf->id,
                        'cabang_id' => $cabangId,
                        'milestone_bulan' => $milestone,
                        'jatuh_tempo' => $jatuhTempo,
                        'nominal' => $nominalKenaikan,
                        'status' => $status,
                    ]);
                }
            }
        }

        return KenaikanGaji::where('cabang_id', $cabangId)
            ->where('status', 'menunggu')
            ->with('user')
            ->orderBy('jatuh_tempo')
            ->get();
    }

    /**
     * Hitung teks masa kerja format "X Tahun Y Bulan".
     */
    public function formatMasaKerja(?Carbon $start, ?Carbon $target = null): string
    {
        if (! $start) {
            return '-';
        }

        $target = $target ?? Carbon::now('Asia/Jakarta');
        if ($start->greaterThan($target)) {
            return '0 Bulan';
        }

        $years = $start->diffInYears($target);
        $months = $start->copy()->addYears($years)->diffInMonths($target);

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} Tahun";
        }
        if ($months > 0 || empty($parts)) {
            $parts[] = "{$months} Bulan";
        }

        return implode(' ', $parts);
    }

    /**
     * Hitung teks usia format "X Tahun Y Bulan".
     */
    public function formatUsia(?Carbon $birthDate, ?Carbon $target = null): string
    {
        if (! $birthDate) {
            return '-';
        }

        $target = $target ?? Carbon::now('Asia/Jakarta');
        if ($birthDate->greaterThan($target)) {
            return '0 Tahun';
        }

        $years = $birthDate->diffInYears($target);
        $months = $birthDate->copy()->addYears($years)->diffInMonths($target);

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} Tahun";
        }
        if ($months > 0 || empty($parts)) {
            $parts[] = "{$months} Bulan";
        }

        return implode(' ', $parts);
    }

    /**
     * Generate nomor unik slip gaji.
     */
    public function generateNoSlip(int $cabangId, int $tahun, int $bulan, int $userId): string
    {
        $cabang = Cabang::find($cabangId);
        $kodeCabang = $cabang ? ($cabang->kode_cabang ?? 'CB') : 'CB';
        $yymm = sprintf('%02d%02d', $tahun % 100, $bulan);

        return sprintf('SLIP-%s-%s-%04d', $kodeCabang, $yymm, $userId);
    }
}

