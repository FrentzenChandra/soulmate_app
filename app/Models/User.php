<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'cabang_id',
        'no_hp',
        'status',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
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

    public function isKasir(): bool
    {
        return $this->hasRole('Kasir');
    }

    public function getRoleNameAttribute(): string
    {
        return $this->getRoleNames()->first() ?? 'Staff';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
}
