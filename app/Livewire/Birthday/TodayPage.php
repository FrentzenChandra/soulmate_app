<?php

namespace App\Livewire\Birthday;

use App\Services\BirthdayService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.birthday')]
#[Title('Selamat Ulang Tahun!')]
class TodayPage extends Component
{
    public array $celebrants = [];

    public function mount(BirthdayService $birthdayService)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // User yang sedang berulang tahun tidak boleh melihat notifikasi tentang dirinya
        if ($birthdayService->isUserBirthdayToday($user)) {
            return redirect()->route('dashboard');
        }

        // Ambil selebran hari ini selain viewer
        $this->celebrants = $birthdayService->getTodayCelebrants($user);

        // Jika tidak ada staf yang ultah hari ini, redirect ke dashboard
        if (empty($this->celebrants)) {
            return redirect()->route('dashboard');
        }
    }

    public function render()
    {
        return view('livewire.birthday.today-page');
    }
}

