<?php

namespace App\Livewire\Web;

use Livewire\Component;

class Kontak extends Component
{
    public string $nama = '';
    public string $email = '';
    public string $no_wa = '';
    public string $subjek = '';
    public string $pesan = '';
    public bool $terkirim = false;

    protected array $rules = [
        'nama' => 'required|min:3|max:100',
        'email' => 'required|email|max:100',
        'no_wa' => 'required|min:8|max:20',
        'subjek' => 'required',
        'pesan' => 'required|min:5|max:1000',
    ];

    protected array $messages = [
        'nama.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
        'subjek.required' => 'Pilih salah satu subjek.',
        'pesan.required' => 'Pesan wajib diisi.',
    ];

    public function kirimPesan(): void
    {
        $this->validate();

        // In production this can be stored in a contact_messages table or sent to WhatsApp / Telegram
        $this->terkirim = true;
        $this->reset(['nama', 'email', 'no_wa', 'subjek', 'pesan']);
    }

    public function resetPesan(): void
    {
        $this->terkirim = false;
    }

    public function render()
    {
        return view('livewire.web.kontak')
            ->layout('layouts.web', ['title' => 'Kontak Kami - Soulmate Komputer']);
    }
}
