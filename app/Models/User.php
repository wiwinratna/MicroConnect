<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_group',   // 'admin' | 'pelakuusaha'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ===================== HELPERS =====================

    public function isAdmin(): bool
    {
        return $this->user_group === 'admin';
    }

    public function isUmkm(): bool
    {
        return $this->user_group === 'pelakuusaha';
    }

    // ===================== RELASI =====================

    public function umkm()
    {
        return $this->hasOne(Umkm::class, 'user_id');
    }
}
