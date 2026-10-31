<?php

namespace App\Livewire\Notification;

use App\Services\BirthdayService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BirthdayNotification extends Component
{
    public bool $showModal = false;

    public array $celebrants = [];

    public ?int $daysLeft = null;

    public function mount(BirthdayService $birthdayService)
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $notif = $birthdayService->getNotification($user);

        if ($notif['phase'] === 'today') {
            return $this->redirect(route('birthday.today'));
        }

        if ($notif['phase'] === 'upcoming' && ! empty($notif['users'])) {
            $this->celebrants = $notif['users'];
            $this->daysLeft = $notif['daysLeft'];
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.notification.birthday-notification');
    }
}

