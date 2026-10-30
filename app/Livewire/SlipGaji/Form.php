<?php

namespace App\Livewire\SlipGaji;

use App\Models\KenaikanGaji;
use App\Models\SlipGaji;
use App\Models\SlipGajiItem;
use App\Models\TarifGaji;
use App\Models\User;
use App\Services\SlipGajiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Form Slip Gaji - Soulmate Komputer')]
class Form extends Component
{
    public ?int $slipId = null;

    // Data Karyawan
    public ?int $selectedUserId = null;
    public int $periodeBulan = 0;
    public int $periodeTahun = 0;

    // Profil Staf Terpilih (Read-only)
    public string $stafNama = '';
    public string $stafJabatan = '';
    public string $stafTanggalLahir = '';
    public string $stafTanggalMulaiBekerja = '';
    public string $stafUsia = '';
    public string $stafMasaKerja = '';
    public string $stafFotoUrl = '';
    public string $stafInisial = '';
    public bool $stafDataLengkap = true;

    // Penerimaan
    public int|float|string|null $gajiPokok = null;
    public int $uangMakanHari = 0;
    public int|float|string|null $uangMakanPerHari = 20000;
    public float $uangMakanTotal = 0;
    public int $uangTransportHari = 0;
    public int|float|string|null $uangTransportPerHari = 20000;
    public float $uangTransportTotal = 0;
    public string $tunjanganNama = 'Tunjangan Kepala Teknisi Laptop';
    public int|float|string|null $tunjanganNominal = null;
    public int|float|string|null $bonusNominal = null;
    public int|float|string|null $lemburPerformaNominal = null;
    public int|float|string|null $servisanSelesaiNominal = null;
    public array $penerimaanItems = [];
    public float $totalBruto = 0;

    // Pengurangan (Daftar Potongan)
    public array $penguranganItems = [];
    public float $totalPengurangan = 0;

    // Total Diterima
    public float $totalDiterima = 0;

    // Status & Sumber
    public string $status = 'draft';
    public ?int $sourceSlipId = null;
    public int|float|string|null $sourceGajiPokok = null;
    public int|float|string|null $sourceUangMakanPerHari = null;
    public int|float|string|null $sourceUangTransportPerHari = null;

    // Alert Kenaikan 6 Bulan
    public ?int $activeKenaikanId = null;
    public ?string $activeKenaikanNama = null;
    public int $activeKenaikanMilestone = 0;
    public float $activeKenaikanNominal = 0;
    public bool $showKenaikanBanner = false;

    // Deteksi Slip Gaji Duplikat (Tanpa Redirect Paksa)
    public ?int $existingSlipId = null;
    public ?string $existingSlipStatus = null;
    public ?string $existingSlipNo = null;

    // Hak Akses
    public bool $canEditGajiPokok = false;
    public bool $isFinal = false;

    public function mount(?int $slip = null): void
    {
        $service = app(SlipGajiService::class);
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.view')), 403, 'Akses Slip Gaji dibatasi.');

        $this->canEditGajiPokok = $user->isOwner() || $user->can('gaji.pokok.edit');

        $now = Carbon::now('Asia/Jakarta');
        $this->periodeBulan = (int) $now->month;
        $this->periodeTahun = (int) $now->year;

        if ($slip) {
            $this->slipId = $slip;
            $this->loadSlip($service);
            if (! $this->isFinal && ! $user->isOwner() && ! $user->can('gaji.edit')) {
                abort(403, 'Akses edit slip gaji dibatasi.');
            }
        } else {
            // Mode Buat Baru
            abort_if(! $user->isOwner() && ! $user->can('gaji.create'), 403, 'Akses buat slip dibatasi.');

            $this->uangMakanPerHari = TarifGaji::getNominal('uang_makan', 20000);
            $this->uangTransportPerHari = TarifGaji::getNominal('uang_transport', 20000);

            // Prioritaskan pilih staf pertama yang BELUM memiliki slip gaji pada periode aktif ini
            $currentCabangId = $this->getCurrentCabangId();
            $sudahAdaSlipUserIds = SlipGaji::where('cabang_id', $currentCabangId)
                ->where('periode_bulan', $this->periodeBulan)
                ->where('periode_tahun', $this->periodeTahun)
                ->pluck('user_id')
                ->toArray();

            $targetStaf = User::role(SlipGajiService::STAF_ROLES)
                ->where('status', 'aktif')
                ->where('cabang_id', $currentCabangId)
                ->whereNotIn('id', $sudahAdaSlipUserIds)
                ->orderBy('name')
                ->first();

            if (! $targetStaf) {
                // Jika semua staf sudah memiliki slip di periode ini, tetap pilih staf pertama
                $targetStaf = User::role(SlipGajiService::STAF_ROLES)
                    ->where('status', 'aktif')
                    ->where('cabang_id', $currentCabangId)
                    ->orderBy('name')
                    ->first();
            }

            if ($targetStaf) {
                $this->selectedUserId = $targetStaf->id;
                $this->onUserSelected($service);
            }
        }

        $this->hitungUlangTotal();
    }

    public function updatedSelectedUserId(): void
    {
        $this->onUserSelected(app(SlipGajiService::class));
    }

    public function updatedPeriodeBulan(): void
    {
        $this->onPeriodeChanged(app(SlipGajiService::class));
    }

    public function updatedPeriodeTahun(): void
    {
        $this->onPeriodeChanged(app(SlipGajiService::class));
    }

    public function updatedGajiPokok(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedUangMakanPerHari(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedUangTransportPerHari(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedTunjanganNominal(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedBonusNominal(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedLemburPerformaNominal(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedServisanSelesaiNominal(): void
    {
        $this->hitungUlangTotal();
    }

    public function updatedPenerimaanItems(): void
    {
        $this->hitungUlangTotal();
    }

    public function checkExistingSlip(): void
    {
        if (! $this->selectedUserId || $this->slipId) {
            $this->existingSlipId = null;
            $this->existingSlipStatus = null;
            $this->existingSlipNo = null;
            return;
        }

        $existing = SlipGaji::where('user_id', $this->selectedUserId)
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->first();

        if ($existing && $existing->id !== $this->slipId) {
            $this->existingSlipId = $existing->id;
            $this->existingSlipStatus = $existing->status;
            $this->existingSlipNo = $existing->no_slip;
        } else {
            $this->existingSlipId = null;
            $this->existingSlipStatus = null;
            $this->existingSlipNo = null;
        }
    }

    public function onUserSelected(SlipGajiService $service): void
    {
        if (! $this->selectedUserId) {
            $this->resetStafProfile();
            return;
        }

        $staf = User::find($this->selectedUserId);
        if (! $staf) {
            $this->resetStafProfile();
            return;
        }

        // Cek apakah slip staf ini pada periode bulan/tahun sudah ada (tanpa me-redirect paksa)
        $this->checkExistingSlip();

        $targetDate = Carbon::createFromDate($this->periodeTahun, $this->periodeBulan, 1);
        $this->stafNama = $staf->name;
        $this->stafJabatan = $staf->bagian ?: 'Belum Diisi';
        $this->stafTanggalLahir = $staf->tanggal_lahir ? $staf->tanggal_lahir->format('d F Y') : 'Belum Dilengkapi';
        $this->stafTanggalMulaiBekerja = $staf->tanggal_mulai_bekerja ? $staf->tanggal_mulai_bekerja->format('d F Y') : 'Belum Dilengkapi';
        $this->stafUsia = $staf->tanggal_lahir ? $service->formatUsia(Carbon::parse($staf->tanggal_lahir), $targetDate) : '-';
        $this->stafMasaKerja = $staf->tanggal_mulai_bekerja ? $service->formatMasaKerja(Carbon::parse($staf->tanggal_mulai_bekerja), $targetDate) : '-';
        $this->stafFotoUrl = $staf->profile_photo_url ?? '';
        $this->stafInisial = strtoupper(substr($staf->name, 0, 2));
        $this->stafDataLengkap = ! empty($staf->tanggal_lahir) && ! empty($staf->tanggal_mulai_bekerja);

        // Jika mode buat baru, tarik nilai dari slip Final terakhir staf
        if (! $this->slipId) {
            $source = $service->ambilSlipFinalTerakhir($staf->id, $this->periodeTahun, $this->periodeBulan);
            if ($source) {
                $this->sourceSlipId = $source->id;
                $this->gajiPokok = (float) $source->gaji_pokok;
                $this->sourceGajiPokok = (float) $source->gaji_pokok;

                $this->uangMakanPerHari = (float) $source->uang_makan_per_hari;
                $this->sourceUangMakanPerHari = (float) $source->uang_makan_per_hari;

                $this->uangTransportPerHari = (float) $source->uang_transport_per_hari;
                $this->sourceUangTransportPerHari = (float) $source->uang_transport_per_hari;

                $this->tunjanganNama = $source->tunjangan_nama ?: 'Tunjangan Kepala Teknisi Laptop';
                $this->tunjanganNominal = (float) $source->tunjangan_nominal;

                // Salin item penerimaan tambahan & potongan jika ada
                $this->penerimaanItems = [];
                $this->penguranganItems = [];
                foreach ($source->items as $idx => $item) {
                    if (($item->tipe ?? 'pengurangan') === 'penerimaan') {
                        $this->penerimaanItems[] = [
                            'nama_item' => $item->nama_item,
                            'nominal' => (float) $item->nominal,
                        ];
                    } else {
                        $this->penguranganItems[] = [
                            'kategori' => $item->kategori ?? 'Lainnya',
                            'nama_item' => $item->nama_item,
                            'nominal' => (float) $item->nominal,
                        ];
                    }
                }
            } else {
                // Slip pertama kali: staf baru belum punya slip final
                $this->sourceSlipId = null;
                $this->gajiPokok = null;
                $this->sourceGajiPokok = null;

                $this->uangMakanPerHari = TarifGaji::getNominal('uang_makan', 20000);
                $this->sourceUangMakanPerHari = $this->uangMakanPerHari;

                $this->uangTransportPerHari = TarifGaji::getNominal('uang_transport', 20000);
                $this->sourceUangTransportPerHari = $this->uangTransportPerHari;

                $this->tunjanganNama = 'Tunjangan Kepala Teknisi Laptop';
                $this->tunjanganNominal = null;
                $this->penerimaanItems = [];
                $this->penguranganItems = [];
            }
        }

        // Hitung hari hadir dari absensi
        $this->uangMakanHari = $service->hitungHariHadir($staf->id, $this->periodeBulan, $this->periodeTahun);
        $this->uangTransportHari = $this->uangMakanHari;

        // Cek alert kenaikan 6 bulan untuk staf ini
        $this->checkKenaikanAlert();

        $this->hitungUlangTotal();
    }

    public function onPeriodeChanged(SlipGajiService $service): void
    {
        if ($this->selectedUserId) {
            $this->uangMakanHari = $service->hitungHariHadir($this->selectedUserId, $this->periodeBulan, $this->periodeTahun);
            $this->uangTransportHari = $this->uangMakanHari;

            $staf = User::find($this->selectedUserId);
            if ($staf) {
                $targetDate = Carbon::createFromDate($this->periodeTahun, $this->periodeBulan, 1);
                $this->stafUsia = $staf->tanggal_lahir ? $service->formatUsia(Carbon::parse($staf->tanggal_lahir), $targetDate) : '-';
                $this->stafMasaKerja = $staf->tanggal_mulai_bekerja ? $service->formatMasaKerja(Carbon::parse($staf->tanggal_mulai_bekerja), $targetDate) : '-';
            }

            $this->checkExistingSlip();
            $this->checkKenaikanAlert();
            $this->hitungUlangTotal();
        }
    }

    public function hitungUlangDariAbsensi(): void
    {
        $service = app(SlipGajiService::class);
        if ($this->selectedUserId) {
            $this->uangMakanHari = $service->hitungHariHadir($this->selectedUserId, $this->periodeBulan, $this->periodeTahun);
            $this->uangTransportHari = $this->uangMakanHari;
            $this->hitungUlangTotal();

            $this->dispatch('swal:toast', [
                'type' => 'info',
                'title' => 'Kehadiran Disinkronkan',
                'message' => "Jumlah hari hadir berhasil dihitung ulang dari data absensi ({$this->uangMakanHari} hari).",
            ]);
        }
    }

    public function salinDariBulanLalu(): void
    {
        $service = app(SlipGajiService::class);
        if (! $this->selectedUserId) {
            return;
        }

        $source = $service->ambilSlipFinalTerakhir($this->selectedUserId, $this->periodeTahun, $this->periodeBulan);
        if (! $source) {
            $this->dispatch('swal:toast', [
                'type' => 'warning',
                'title' => 'Tidak Ada Slip Final',
                'message' => 'Karyawan ini belum memiliki riwayat slip gaji berstatus Final pada bulan-bulan sebelumnya.',
            ]);
            return;
        }

        $this->sourceSlipId = $source->id;
        $this->gajiPokok = (float) $source->gaji_pokok;
        $this->sourceGajiPokok = (float) $source->gaji_pokok;

        $this->uangMakanPerHari = (float) $source->uang_makan_per_hari;
        $this->sourceUangMakanPerHari = (float) $source->uang_makan_per_hari;

        $this->uangTransportPerHari = (float) $source->uang_transport_per_hari;
        $this->sourceUangTransportPerHari = (float) $source->uang_transport_per_hari;

        $this->tunjanganNama = $source->tunjangan_nama ?: 'Tunjangan Kepala Teknisi Laptop';
        $this->tunjanganNominal = (float) $source->tunjangan_nominal;

        $this->penerimaanItems = [];
        $this->penguranganItems = [];
        foreach ($source->items as $item) {
            if (($item->tipe ?? 'pengurangan') === 'penerimaan') {
                $this->penerimaanItems[] = [
                    'nama_item' => $item->nama_item,
                    'nominal' => (float) $item->nominal,
                ];
            } else {
                $this->penguranganItems[] = [
                    'kategori' => $item->kategori ?? 'Lainnya',
                    'nama_item' => $item->nama_item,
                    'nominal' => (float) $item->nominal,
                ];
            }
        }

        $this->hitungUlangTotal();

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Berhasil Disalin',
            'message' => "Data gaji pokok, tunjangan, dan per hari disalin dari slip Final periode {$source->periode_nama}.",
        ]);
    }

    public function resetForm(): void
    {
        $service = app(SlipGajiService::class);
        if ($this->slipId) {
            $this->loadSlip($service);
        } else {
            $this->onUserSelected($service);
        }

        $this->dispatch('swal:toast', [
            'type' => 'info',
            'title' => 'Form Direset',
            'message' => 'Nilai formulir telah dikembalikan ke kondisi awal.',
        ]);
    }

    public function checkKenaikanAlert(): void
    {
        $this->showKenaikanBanner = false;
        $this->activeKenaikanId = null;

        if (! $this->selectedUserId) {
            return;
        }

        $kenaikan = KenaikanGaji::where('user_id', $this->selectedUserId)
            ->where('status', 'menunggu')
            ->orderBy('jatuh_tempo')
            ->first();

        if ($kenaikan) {
            $this->activeKenaikanId = $kenaikan->id;
            $this->activeKenaikanNama = $kenaikan->user ? $kenaikan->user->name : $this->stafNama;
            $this->activeKenaikanMilestone = $kenaikan->milestone_bulan;
            $this->activeKenaikanNominal = (float) $kenaikan->nominal;
            $this->showKenaikanBanner = true;
        }
    }

    public function terapkanKenaikan(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.pokok.edit')), 403, 'Akses ubah gaji pokok dibatasi.');

        if (! $this->activeKenaikanId) {
            return;
        }

        $kenaikan = KenaikanGaji::find($this->activeKenaikanId);
        if ($kenaikan && $kenaikan->status === 'menunggu') {
            $this->gajiPokok = round($this->gajiPokok + (float) $kenaikan->nominal, 2);
            $this->hitungUlangTotal();

            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'Kenaikan Diterapkan',
                'message' => "Gaji pokok berhasil dinaikkan Rp " . number_format($kenaikan->nominal, 0, ',', '.') . ".",
            ]);
        }
    }

    public function tandaiKenaikanDitangani(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.pokok.edit')), 403, 'Akses ubah gaji pokok dibatasi.');

        if (! $this->activeKenaikanId) {
            return;
        }

        $kenaikan = KenaikanGaji::find($this->activeKenaikanId);
        if ($kenaikan) {
            $kenaikan->update([
                'status' => 'ditandai',
                'ditangani_by' => $user->id,
                'ditangani_at' => Carbon::now('Asia/Jakarta'),
            ]);

            $this->showKenaikanBanner = false;
            $this->activeKenaikanId = null;

            $this->dispatch('swal:toast', [
                'type' => 'info',
                'title' => 'Status Diperbarui',
                'message' => 'Pengingat kenaikan gaji pokok telah ditandai sudah ditangani.',
            ]);
        }
    }

    public function tambahPenerimaan(): void
    {
        $this->penerimaanItems[] = [
            'nama_item' => '',
            'nominal' => '',
        ];
        $this->hitungUlangTotal();
    }

    public function hapusPenerimaan(int $index): void
    {
        unset($this->penerimaanItems[$index]);
        $this->penerimaanItems = array_values($this->penerimaanItems);
        $this->hitungUlangTotal();
    }

    public function tambahPotongan(): void
    {
        $this->penguranganItems[] = [
            'kategori' => 'Lainnya',
            'nama_item' => '',
            'nominal' => '',
        ];
        $this->hitungUlangTotal();
    }

    public function hapusPotongan(int $index): void
    {
        unset($this->penguranganItems[$index]);
        $this->penguranganItems = array_values($this->penguranganItems);
        $this->hitungUlangTotal();
    }

    public function updatedPenguranganItems(): void
    {
        // Bebas edit nama_item tanpa tertimpa kategori
        $this->hitungUlangTotal();
    }

    public function hitungUlangTotal(): void
    {
        $service = app(SlipGajiService::class);
        $calc = $service->kalkulasiTotal([
            'gaji_pokok' => (float) ($this->gajiPokok ?? 0),
            'uang_makan_hari' => $this->uangMakanHari,
            'uang_makan_per_hari' => (float) ($this->uangMakanPerHari ?? 0),
            'uang_transport_hari' => $this->uangTransportHari,
            'uang_transport_per_hari' => (float) ($this->uangTransportPerHari ?? 0),
            'tunjangan_nominal' => (float) ($this->tunjanganNominal ?? 0),
            'bonus_nominal' => (float) ($this->bonusNominal ?? 0),
            'lembur_performa_nominal' => (float) ($this->lemburPerformaNominal ?? 0),
            'servisan_selesai_nominal' => (float) ($this->servisanSelesaiNominal ?? 0),
        ], $this->penguranganItems, $this->penerimaanItems);

        $this->uangMakanTotal = $calc['uang_makan_total'];
        $this->uangTransportTotal = $calc['uang_transport_total'];
        $this->totalBruto = $calc['total_bruto'];
        $this->totalPengurangan = $calc['total_pengurangan'];
        $this->totalDiterima = $calc['total_diterima'];
    }

    public function saveDraft(): void
    {
        $user = Auth::user();
        if ($this->slipId) {
            abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.edit')), 403, 'Akses edit komponen slip gaji dibatasi.');
        } else {
            abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.create')), 403, 'Akses buat slip gaji dibatasi.');
        }

        if (! $this->slipId && $this->existingSlipId) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Slip Gaji Sudah Ada',
                'message' => 'Karyawan ini telah memiliki slip gaji untuk periode yang dipilih.',
            ]);
            return;
        }

        $this->processSave('draft', app(SlipGajiService::class));
    }

    public function finalize(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.finalize')), 403, 'Akses finalkan slip dibatasi.');

        if (! $this->slipId && $this->existingSlipId) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Slip Gaji Sudah Ada',
                'message' => 'Karyawan ini telah memiliki slip gaji untuk periode yang dipilih.',
            ]);
            return;
        }

        if ((float) ($this->gajiPokok ?? 0) <= 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Gaji Pokok Kosong',
                'message' => 'Gaji pokok wajib diisi sebelum slip gaji dapat difinalkan.',
            ]);
            return;
        }

        if ($this->totalDiterima < 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Total Diterima Negatif',
                'message' => 'Slip gaji tidak dapat difinalkan karena total potongan melebihi penghasilan bruto.',
            ]);
            return;
        }

        $this->processSave('final', app(SlipGajiService::class));
    }

    private function processSave(string $targetStatus, SlipGajiService $service): void
    {
        $user = Auth::user();
        abort_if(! $user, 403);

        $staf = User::findOrFail($this->selectedUserId);
        $cabangId = $staf->cabang_id ?? 1;

        // SERVER-SIDE GUARD UNTUK GAJI POKOK & TARIF PER HARI
        // Jika user TIDAK punya izin gaji.pokok.edit, abaikan nilai kiriman klien dan paksa nilai lama
        if (! $this->canEditGajiPokok) {
            if ($this->sourceGajiPokok !== null) {
                $this->gajiPokok = $this->sourceGajiPokok;
            }
            if ($this->sourceUangMakanPerHari !== null) {
                $this->uangMakanPerHari = $this->sourceUangMakanPerHari;
            }
            if ($this->sourceUangTransportPerHari !== null) {
                $this->uangTransportPerHari = $this->sourceUangTransportPerHari;
            }
        }

        // Hitung ulang hari hadir dari absensi saat Final
        if ($targetStatus === 'final') {
            $this->uangMakanHari = $service->hitungHariHadir($staf->id, $this->periodeBulan, $this->periodeTahun);
            $this->uangTransportHari = $this->uangMakanHari;
        }

        $this->hitungUlangTotal();

        // Cek jejak audit nilai tetap
        $nilaiTetapDiubah = false;
        if ($this->canEditGajiPokok && $this->sourceGajiPokok !== null) {
            if (
                round((float) ($this->gajiPokok ?? 0), 2) !== round((float) ($this->sourceGajiPokok ?? 0), 2) ||
                round((float) ($this->uangMakanPerHari ?? 0), 2) !== round((float) ($this->sourceUangMakanPerHari ?? 0), 2) ||
                round((float) ($this->uangTransportPerHari ?? 0), 2) !== round((float) ($this->sourceUangTransportPerHari ?? 0), 2)
            ) {
                $nilaiTetapDiubah = true;
            }
        }

        $targetDate = Carbon::createFromDate($this->periodeTahun, $this->periodeBulan, 1);
        $masaKerja = $this->stafMasaKerja ?: $service->formatMasaKerja($staf->tanggal_mulai_bekerja ? Carbon::parse($staf->tanggal_mulai_bekerja) : null, $targetDate);
        $usia = $this->stafUsia ?: $service->formatUsia($staf->tanggal_lahir ? Carbon::parse($staf->tanggal_lahir) : null, $targetDate);

        DB::transaction(function () use ($cabangId, $staf, $targetStatus, $nilaiTetapDiubah, $masaKerja, $usia, $user, $service) {
            $slip = $this->slipId ? SlipGaji::find($this->slipId) : null;

            if ($slip && $slip->isFinal() && $targetStatus !== 'draft') {
                abort(403, 'Slip Final tidak dapat diubah.');
            }

            $noSlip = $slip ? $slip->no_slip : $service->generateNoSlip($cabangId, $this->periodeTahun, $this->periodeBulan, $staf->id);

            $data = [
                'no_slip' => $noSlip,
                'cabang_id' => $cabangId,
                'user_id' => $staf->id,
                'periode_bulan' => $this->periodeBulan,
                'periode_tahun' => $this->periodeTahun,
                'status' => $targetStatus,
                'source_slip_id' => $this->sourceSlipId,

                // Snapshot Staf (Dibekukan saat Final atau diisi draft)
                'nama_staf_snapshot' => $staf->name,
                'bagian_snapshot' => $staf->bagian ?: '-',
                'tanggal_lahir_snapshot' => $staf->tanggal_lahir,
                'tanggal_mulai_bekerja_snapshot' => $staf->tanggal_mulai_bekerja,
                'masa_kerja_snapshot' => $masaKerja,
                'usia_snapshot' => $usia,

                'gaji_pokok' => (float) ($this->gajiPokok ?? 0),
                'uang_makan_hari' => $this->uangMakanHari,
                'uang_makan_per_hari' => (float) ($this->uangMakanPerHari ?? 0),
                'uang_makan_total' => $this->uangMakanTotal,

                'uang_transport_hari' => $this->uangTransportHari,
                'uang_transport_per_hari' => (float) ($this->uangTransportPerHari ?? 0),
                'uang_transport_total' => $this->uangTransportTotal,

                'tunjangan_nama' => $this->tunjanganNama,
                'tunjangan_nominal' => (float) ($this->tunjanganNominal ?? 0),
                'bonus_nominal' => (float) ($this->bonusNominal ?? 0),
                'lembur_performa_nominal' => (float) ($this->lemburPerformaNominal ?? 0),
                'servisan_selesai_nominal' => (float) ($this->servisanSelesaiNominal ?? 0),

                'total_bruto' => $this->totalBruto,
                'total_pengurangan' => $this->totalPengurangan,
                'total_diterima' => $this->totalDiterima,
            ];

            if ($nilaiTetapDiubah) {
                $data['diubah_nilai_tetap_by'] = $user->id;
                $data['diubah_nilai_tetap_at'] = Carbon::now('Asia/Jakarta');
            }

            if ($targetStatus === 'final') {
                $data['finalized_by'] = $user->id;
                $data['finalized_at'] = Carbon::now('Asia/Jakarta');
            }

            if ($slip) {
                $slip->update($data);
            } else {
                $data['created_by'] = $user->id;
                $slip = SlipGaji::create($data);
                $this->slipId = $slip->id;
            }

            // Simpan rincian items (penerimaan tambahan & pengurangan)
            $slip->items()->delete();
            $urutan = 1;

            foreach ($this->penerimaanItems as $item) {
                if ((float) ($item['nominal'] ?? 0) > 0 || ! empty(trim($item['nama_item'] ?? ''))) {
                    SlipGajiItem::create([
                        'slip_gaji_id' => $slip->id,
                        'tipe' => 'penerimaan',
                        'kategori' => 'Penerimaan',
                        'nama_item' => ! empty(trim($item['nama_item'] ?? '')) ? trim($item['nama_item']) : 'Penerimaan Tambahan',
                        'nominal' => round((float) ($item['nominal'] ?? 0), 2),
                        'urutan' => $urutan++,
                    ]);
                }
            }

            foreach ($this->penguranganItems as $item) {
                if ((float) ($item['nominal'] ?? 0) > 0 || ! empty(trim($item['nama_item'] ?? ''))) {
                    SlipGajiItem::create([
                        'slip_gaji_id' => $slip->id,
                        'tipe' => 'pengurangan',
                        'kategori' => $item['kategori'] ?? 'Lainnya',
                        'nama_item' => ! empty(trim($item['nama_item'] ?? '')) ? trim($item['nama_item']) : 'Potongan',
                        'nominal' => round((float) ($item['nominal'] ?? 0), 2),
                        'urutan' => $urutan++,
                    ]);
                }
            }

            // Jika finalkan dan ada kenaikan gaji berstatus menunggu, cek apakah gaji pokok >= gaji pokok sumber + nominal
            if ($targetStatus === 'final' && $this->activeKenaikanId) {
                $kenaikan = KenaikanGaji::find($this->activeKenaikanId);
                if ($kenaikan && $kenaikan->status === 'menunggu') {
                    $threshold = (float) ($this->sourceGajiPokok ?? 0) + (float) $kenaikan->nominal;
                    if ((float) ($this->gajiPokok ?? 0) >= $threshold) {
                        $kenaikan->update([
                            'status' => 'diterapkan',
                            'slip_gaji_id' => $slip->id,
                            'ditangani_by' => $user->id,
                            'ditangani_at' => Carbon::now('Asia/Jakarta'),
                        ]);
                    }
                }
            }

            $this->status = $targetStatus;
            $this->isFinal = ($targetStatus === 'final');
        });

        $pesan = $targetStatus === 'final' ? 'Slip gaji berhasil difinalkan dan dibekukan.' : 'Draft slip gaji berhasil disimpan.';
        session()->flash('success', $pesan);
        $this->redirectRoute('slip-gaji.index', navigate: true);
    }

    private function loadSlip(SlipGajiService $service): void
    {
        $slip = SlipGaji::with('items')->findOrFail($this->slipId);

        $this->isFinal = $slip->isFinal();
        $this->status = $slip->status;
        $this->selectedUserId = $slip->user_id;
        $this->periodeBulan = $slip->periode_bulan;
        $this->periodeTahun = $slip->periode_tahun;
        $this->sourceSlipId = $slip->source_slip_id;

        $staf = $slip->user;
        $this->stafNama = $slip->nama_staf_snapshot ?: ($staf ? $staf->name : '-');
        $this->stafJabatan = $slip->bagian_snapshot ?: ($staf ? $staf->bagian : '-');
        $this->stafTanggalLahir = $slip->tanggal_lahir_snapshot ? $slip->tanggal_lahir_snapshot->format('d F Y') : '-';
        $this->stafTanggalMulaiBekerja = $slip->tanggal_mulai_bekerja_snapshot ? $slip->tanggal_mulai_bekerja_snapshot->format('d F Y') : '-';
        $this->stafUsia = $slip->usia_snapshot ?: '-';
        $this->stafMasaKerja = $slip->masa_kerja_snapshot ?: '-';
        $this->stafFotoUrl = $staf ? ($staf->profile_photo_url ?? '') : '';
        $this->stafInisial = strtoupper(substr($this->stafNama, 0, 2));

        $this->gajiPokok = (float) $slip->gaji_pokok > 0 ? (float) $slip->gaji_pokok : null;
        $this->uangMakanHari = $slip->uangMakanHari ?: $slip->uang_makan_hari;
        $this->uangMakanPerHari = (float) $slip->uang_makan_per_hari > 0 ? (float) $slip->uang_makan_per_hari : null;
        $this->uangMakanTotal = (float) $slip->uang_makan_total;

        $this->uangTransportHari = $slip->uangTransportHari ?: $slip->uang_transport_hari;
        $this->uangTransportPerHari = (float) $slip->uang_transport_per_hari > 0 ? (float) $slip->uang_transport_per_hari : null;
        $this->uangTransportTotal = (float) $slip->uang_transport_total;

        $this->tunjanganNama = $slip->tunjangan_nama ?: 'Tunjangan Kepala Teknisi Laptop';
        $this->tunjanganNominal = (float) $slip->tunjangan_nominal > 0 ? (float) $slip->tunjangan_nominal : null;
        $this->bonusNominal = (float) $slip->bonus_nominal > 0 ? (float) $slip->bonus_nominal : null;
        $this->lemburPerformaNominal = (float) $slip->lembur_performa_nominal > 0 ? (float) $slip->lembur_performa_nominal : null;
        $this->servisanSelesaiNominal = (float) $slip->servisan_selesai_nominal > 0 ? (float) $slip->servisan_selesai_nominal : null;

        $this->penerimaanItems = [];
        $this->penguranganItems = [];
        foreach ($slip->items as $item) {
            if (($item->tipe ?? 'pengurangan') === 'penerimaan') {
                $this->penerimaanItems[] = [
                    'nama_item' => $item->nama_item,
                    'nominal' => (float) $item->nominal > 0 ? (float) $item->nominal : '',
                ];
            } else {
                $this->penguranganItems[] = [
                    'kategori' => $item->kategori ?? 'Lainnya',
                    'nama_item' => $item->nama_item,
                    'nominal' => (float) $item->nominal > 0 ? (float) $item->nominal : '',
                ];
            }
        }

        // Backward compatibility: jika ada data penerimaan lama di kolom slip tapi belum ada di items
        if (empty($this->penerimaanItems)) {
            if ((float) $slip->bonus_nominal > 0) {
                $this->penerimaanItems[] = [
                    'nama_item' => 'Bonus',
                    'nominal' => (float) $slip->bonus_nominal,
                ];
            }
            if ((float) $slip->lembur_performa_nominal > 0) {
                $this->penerimaanItems[] = [
                    'nama_item' => 'Lembur/Performa',
                    'nominal' => (float) $slip->lembur_performa_nominal,
                ];
            }
            if ((float) $slip->servisan_selesai_nominal > 0) {
                $this->penerimaanItems[] = [
                    'nama_item' => 'Servisan Selesai',
                    'nominal' => (float) $slip->servisan_selesai_nominal,
                ];
            }
            if ((float) $slip->tunjangan_nominal > 0) {
                $this->penerimaanItems[] = [
                    'nama_item' => $slip->tunjangan_nama ?: 'Tunjangan',
                    'nominal' => (float) $slip->tunjangan_nominal,
                ];
            }
        }

        // Ambil baseline sumber jika ada
        if ($slip->source_slip_id) {
            $source = SlipGaji::find($slip->source_slip_id);
            if ($source) {
                $this->sourceGajiPokok = (float) $source->gaji_pokok;
                $this->sourceUangMakanPerHari = (float) $source->uang_makan_per_hari;
                $this->sourceUangTransportPerHari = (float) $source->uang_transport_per_hari;
            }
        } else {
            $this->sourceGajiPokok = $this->gajiPokok;
            $this->sourceUangMakanPerHari = $this->uangMakanPerHari;
            $this->sourceUangTransportPerHari = $this->uangTransportPerHari;
        }

        $this->checkKenaikanAlert();
    }

    private function resetStafProfile(): void
    {
        $this->stafNama = '';
        $this->stafJabatan = '';
        $this->stafTanggalLahir = '';
        $this->stafTanggalMulaiBekerja = '';
        $this->stafUsia = '';
        $this->stafMasaKerja = '';
        $this->stafFotoUrl = '';
        $this->stafInisial = '';
    }

    private function getCurrentCabangId(): int
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        if ($isOwner && $activeBranchSession && $activeBranchSession !== 'hq') {
            return (int) $activeBranchSession;
        }

        return (int) ($user->cabang_id ?? 1);
    }

    public function render()
    {
        $this->hitungUlangTotal();

        $currentCabangId = $this->getCurrentCabangId();
        $stafs = User::role(SlipGajiService::STAF_ROLES)
            ->where('status', 'aktif')
            ->where('cabang_id', $currentCabangId)
            ->orderBy('name')
            ->get();

        $slipModel = $this->slipId ? SlipGaji::with(['items', 'user', 'cabang'])->find($this->slipId) : null;

        return view('livewire.slip-gaji.form', [
            'stafs' => $stafs,
            'slipModel' => $slipModel,
        ]);
    }
}
