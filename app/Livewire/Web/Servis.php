<?php

namespace App\Livewire\Web;

use App\Models\ServisMasuk;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.web')]
#[Title('Layanan Servis - Soulmate Komputer')]
class Servis extends Component
{
    // Mode tampilan: 'cek' (Cek Status Servis) atau 'jemput' (Ajukan Jemput Servisan)
    public string $activeMode = 'cek';

    // State Cek Status Servis
    public string $kodeInput = '';
    public ?ServisMasuk $servisFound = null;
    public bool $hasSearched = false;
    public ?string $errorMessage = null;

    // State Formulir Jemput Servisan
    public string $jemputNama = '';
    public string $jemputWhatsapp = '';
    public string $jemputPerangkat = '';
    public int $jemputJumlah = 1;
    public string $jemputAlamat = '';
    public string $jemputMapsUrl = '';
    public string $jemputKeluhan = '';
    public string $jemputJadwalTipe = 'segera'; // 'segera' | 'terjadwal'
    public string $jemputTanggal = '';
    public string $jemputWaktu = '';

    public function mount(): void
    {
        if (request()->query('mode') === 'jemput') {
            $this->activeMode = 'jemput';
        }

        if (request()->has('kode')) {
            $this->activeMode = 'cek';
            $this->kodeInput = trim((string) request()->query('kode'));
            $this->cekStatus();
        }
    }

    public function openJemputMode(): void
    {
        $this->activeMode = 'jemput';
        $this->resetValidation();
    }

    public function openCekMode(): void
    {
        $this->activeMode = 'cek';
        $this->resetValidation();
    }

    public function incrementJumlah(): void
    {
        if ($this->jemputJumlah < 99) {
            $this->jemputJumlah++;
        }
    }

    public function decrementJumlah(): void
    {
        if ($this->jemputJumlah > 1) {
            $this->jemputJumlah--;
        }
    }

    public function kirimJemputKeWA()
    {
        $rules = [
            'jemputNama' => 'required|string|min:2|max:100',
            'jemputWhatsapp' => 'required|string|min:8|max:25',
            'jemputPerangkat' => 'required|string|min:2|max:150',
            'jemputJumlah' => 'required|integer|min:1|max:99',
            'jemputAlamat' => 'required|string|min:5|max:500',
            'jemputKeluhan' => 'required|string|min:3|max:500',
            'jemputJadwalTipe' => 'required|in:segera,terjadwal',
        ];

        if ($this->jemputJadwalTipe === 'terjadwal') {
            $rules['jemputTanggal'] = 'required|date';
            $rules['jemputWaktu'] = 'required|string';
        }

        $messages = [
            'jemputNama.required' => 'Nama lengkap wajib diisi.',
            'jemputWhatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'jemputPerangkat.required' => 'Jenis perangkat wajib diisi.',
            'jemputAlamat.required' => 'Alamat jemput wajib diisi.',
            'jemputKeluhan.required' => 'Keluhan atau keterangan wajib diisi.',
            'jemputTanggal.required' => 'Silakan pilih tanggal penjemputan.',
            'jemputWaktu.required' => 'Silakan pilih waktu penjemputan.',
        ];

        $this->validate($rules, $messages);

        $jadwalText = $this->jemputJadwalTipe === 'segera'
            ? 'Segera'
            : "{$this->jemputTanggal} ({$this->jemputWaktu})";

        $lines = [
            "Halo Admin *Soulmate Komputer*, saya ingin mengajukan *Jemput Servisan*:",
            "",
            "*DATA PELANGGAN*",
            "• Nama: {$this->jemputNama}",
            "• WhatsApp: {$this->jemputWhatsapp}",
            "• Jenis: {$this->jemputPerangkat}",
            "• Jumlah: {$this->jemputJumlah} Unit",
            "• Keluhan: {$this->jemputKeluhan}",
            "• Alamat: {$this->jemputAlamat}",
        ];

        if (! empty($this->jemputMapsUrl)) {
            $lines[] = "• Maps: {$this->jemputMapsUrl}";
        }

        $lines[] = "• Jadwal: {$jadwalText}";
        $lines[] = "";
        $lines[] = "Mohon konfirmasi jadwal penjemputannya. Terima kasih!";

        $message = implode("\n", $lines);
        $waUrl = 'https://wa.me/6281995466255?text=' . urlencode($message);

        return redirect()->away($waUrl);
    }

    public function cekStatus(): void
    {
        $this->errorMessage = null;
        $this->hasSearched = true;

        $kode = trim($this->kodeInput);
        if (empty($kode)) {
            $this->servisFound = null;
            $this->errorMessage = 'Silakan masukkan kode servis Anda.';
            return;
        }

        // Normalize code: remove optional 'INV-' or 'SV-' or 'SK-' prefix to match date-sequence
        $norm = preg_replace('/^(INV-)?(SV|SK)-?/i', '', $kode);

        $this->servisFound = ServisMasuk::with(['customer', 'teknisi', 'statusLogs', 'cabang'])
            ->where(function ($q) use ($kode, $norm) {
                $q->where('no_servis', 'like', "%{$kode}%")
                  ->orWhere('no_servis', 'like', "%{$norm}%")
                  ->orWhere('tipe_unit', 'like', "%{$kode}%")
                  ->orWhere('serial_number', 'like', "%{$kode}%");
            })
            ->latest('tanggal_masuk')
            ->first();

        if (! $this->servisFound) {
            $this->errorMessage = "Tiket servis dengan kode '{$kode}' tidak ditemukan. Mohon periksa kembali nomor servis pada lembar tanda terima Anda atau hubungi layanan pelanggan kami.";
        }
    }

    public function resetSearch(): void
    {
        $this->kodeInput = '';
        $this->servisFound = null;
        $this->hasSearched = false;
        $this->errorMessage = null;
    }

    public function render()
    {
        return view('livewire.web.servis');
    }
}
