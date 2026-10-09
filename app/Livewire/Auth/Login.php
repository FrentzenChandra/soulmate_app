<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    public ?string $turnstileToken = null;

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

        // 1. Native Laravel Rate Limiter (Anti Brute-Force - Ramah Pengujian)
        $throttleKey = Str::transliterate(Str::lower($this->identity) . '|' . request()->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'identity' => "Terlalu banyak percobaan login gagal. Silakan tunggu {$seconds} detik.",
            ]);
        }

        // 2. Optional Captcha Verification (Cloudflare Turnstile)
        $secretKey = config('services.turnstile.secret_key');
        if (! empty($secretKey)) {
            $token = request()->input('cf-turnstile-response') ?? $this->turnstileToken;
            if (empty($token)) {
                throw ValidationException::withMessages([
                    'turnstileToken' => 'Silakan selesaikan verifikasi captcha terlebih dahulu.',
                ]);
            }

            try {
                $verify = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);

                if (! $verify->json('success')) {
                    $this->turnstileToken = null;
                    throw ValidationException::withMessages([
                        'turnstileToken' => 'Verifikasi captcha tidak valid atau sudah kedaluwarsa. Silakan coba lagi.',
                    ]);
                }
            } catch (ValidationException $ve) {
                throw $ve;
            } catch (\Throwable $e) {
                // If Cloudflare verification network timeout or offline, log and proceed gracefully
            }
        }

        // 3. Cek login via email atau username
        $loginField = filter_var($this->identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $this->identity,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'identity' => 'Email / Username atau kata sandi Tidak Valid.',
            ]);
        }

        // 4. Cek status user aktif
        $user = Auth::user();
        if ($user->status !== 'aktif') {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'identity' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Owner/Administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
