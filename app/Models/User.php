<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Peran;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'prodi_id', 'wajib_ganti_sandi'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable;

    protected $attributes = ['aktif' => true];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->aktif && $this->roles()->exists();
    }

    /**
     * Peran yang mengurus data kerja sama. Super Admin sengaja tidak termasuk:
     * ia hanya mengelola pengguna dan pengaturan sistem.
     */
    public function mengurusKerjaSama(): bool
    {
        return $this->hasAnyRole([
            Peran::AdminFakultas->value,
            Peran::AdminProdi->value,
            Peran::Pimpinan->value,
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'aktif' => 'boolean',
            'wajib_ganti_sandi' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
