<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
        'rejected_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'is_verified' => 'boolean',
            'email_verified_at' => 'datetime',
            'rejected_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Apakah akun sudah terverifikasi (aktif dan dapat login).
     */
    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }

    /**
     * Apakah akun ditolak oleh admin (belum diverifikasi + punya jejak penolakan).
     */
    public function isRejected(): bool
    {
        return ! $this->is_verified && $this->rejected_at !== null;
    }

    /**
     * Apakah akun masih menunggu verifikasi (belum terverifikasi & belum ditolak).
     */
    public function isPending(): bool
    {
        return ! $this->is_verified && $this->rejected_at === null;
    }

    /**
     * Label status akun untuk tampilan admin.
     */
    public function statusLabel(): string
    {
        return match (true) {
            $this->isVerified() => 'Terverifikasi',
            $this->isRejected() => 'Ditolak',
            default => 'Menunggu Verifikasi',
        };
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_verified', false)->whereNull('rejected_at');
    }

    public function scopeRejected($query)
    {
        return $query->where('is_verified', false)->whereNotNull('rejected_at');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
