<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login Admin & Staff')]
class Login extends Component
{
    public string $identity = '';

    public string $password = '';

    public bool $remember = false;

    public function rules(): array
    {
        return [
            'identity' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'identity.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }


    public function authenticate()
    {
        $this->validate();

        // Cek login via email atau username
        $loginField = filter_var($this->identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $this->identity,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, $this->remember)) {
            throw ValidationException::withMessages([
                'identity' => 'Email / Username atau kata sandi Tidak Valid.',
            ]);
        }

        // Cek status user aktif
        $user = Auth::user();
        if ($user->status !== 'aktif') {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'identity' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Owner/Administrator.',
            ]);
        }

        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
