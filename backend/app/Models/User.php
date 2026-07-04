<?php

namespace App\Models;

use App\Contracts\UserRepositoryInterface;
use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'timezone',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        static::created(function (User $user) {
            $user->profile()->create();
        });

        static::deleting(function (User $user) {
            if ($user->isForceDeleting()) {
                return;
            }
            $user->tokens()->each(fn (PersonalAccessToken $token) => $token->delete());
            $user->email = 'deleted-' . $user->uuid . '@anonymous';
            $user->name = 'Deleted User';
            $user->is_active = false;
            $user->save();
        });
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin->value;
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)->firstOrFail();
    }
}
