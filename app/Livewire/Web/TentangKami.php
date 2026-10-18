<?php

namespace App\Livewire\Web;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.web')]
#[Title('Tentang Kami - Soulmate Komputer')]
class TentangKami extends Component
{
    public function render()
    {
        return view('livewire.web.tentang-kami');
    }
}
