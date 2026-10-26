<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'cabang_id',
        'no_hp',
        'status',
        'foto_url',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function absensis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function isOwner(): bool
    {
        return $this->hasRole('Owner');
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    public function isTeknisi(): bool
    {
        return $this->hasRole('Teknisi');
    }

    public function isBendahara(): bool
    {
        return $this->hasRole('Bendahara');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin') || $this->hasRole('Kasir');
    }

    public function isKasir(): bool
    {
        return $this->hasRole('Admin') || $this->hasRole('Kasir');
    }

    public function isMagang(): bool
    {
        return $this->hasRole('Magang');
    }

    public function isOperasional(): bool
    {
        return $this->hasRole('Operasional');
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path);
        }

        if ($this->foto_url) {
            return asset($this->foto_url);
        }

        return $this->defaultProfilePhotoUrl();
    }

    public function getRoleNameAttribute(): string
    {
        return $this->getRoleNames()->first() ?? 'Staff';
    }
}
