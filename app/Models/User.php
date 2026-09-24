<?php

namespace App\Models;

use App\Enums\PeranUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'google_id', 'peran', 'status_aktif', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'peran' => PeranUser::class,
            'status_aktif' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isSuperadmin(): bool { return $this->peran === PeranUser::Superadmin; }
    public function isAktif(): bool { return $this->status_aktif; }

    public function isPengelolaSpmb(): bool
    {
        return $this->isAktif() && in_array($this->peran, [PeranUser::Superadmin, PeranUser::AdminSpmb], true);
    }
}
