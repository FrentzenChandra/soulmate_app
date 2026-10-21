<?php

namespace App\Livewire\Absensi;

use App\Models\Absensi;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Absensi Staf')]
#[Layout('layouts.app')]
class Index extends Component
{
    public string $statusState = 'siap'; // siap, memproses, sudah_absen, bukan_wifi
    public ?array $todayAbsensi = null;
    public string $clientIp = '';
    public bool $isWifiValid = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public function mount(AbsensiService $service): void
    {
        $user = Auth::user();

        // Otorisasi akses: Staf Cabang (Superadmin, Admin, Teknisi, Bendahara, Kasir) dan Owner
        if (! $user->isOwner() && ! $user->hasAnyRole(['Superadmin', 'Admin', 'Teknisi', 'Bendahara', 'Kasir'])) {
            abort(403, 'Akses absensi dibatasi untuk staf cabang.');
        }

        $this->refreshAttendanceStatus($service);
    }

    public function refreshAttendanceStatus(AbsensiService $service): void
    {
        $user = Auth::user();
        $this->clientIp = trim(request()->ip() ?? '127.0.0.1');

        $absen = $service->statusHariIni($user);

        if ($absen) {
            $this->todayAbsensi = [
                'id' => $absen->id,
                'waktu_absen' => $absen->waktu_absen->format('H:i:s'),
                'jam_absen' => $absen->waktu_absen->format('H:i'),
                'tanggal' => $absen->tanggal->translatedFormat('d F Y'),
                'ip_address' => $absen->ip_address,
            ];
            $this->statusState = 'sudah_absen';
            $this->isWifiValid = true;
            return;
        }

        $this->isWifiValid = $service->jaringanValid($user, $this->clientIp);

        if ($this->isWifiValid) {
            $this->statusState = 'siap';
        } else {
            $this->statusState = 'bukan_wifi';
        }
    }

    public function submitAbsen(AbsensiService $service): void
    {
        $user = Auth::user();
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $clientIp = trim(request()->ip() ?? '127.0.0.1');
            $userAgent = request()->userAgent();

            $absen = $service->absen($user, $clientIp, $userAgent);

            $this->todayAbsensi = [
                'id' => $absen->id,
                'waktu_absen' => $absen->waktu_absen->format('H:i:s'),
                'jam_absen' => $absen->waktu_absen->format('H:i'),
                'tanggal' => $absen->tanggal->translatedFormat('d F Y'),
                'ip_address' => $absen->ip_address,
            ];
            $this->statusState = 'sudah_absen';
            $this->successMessage = 'Absensi berhasil dicatat pada jam ' . $absen->waktu_absen->format('H:i') . ' WIB.';
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->refreshAttendanceStatus($service);
        }
    }

    public function render()
    {
        return view('livewire.absensi.index');
    }
}

