<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_staff',
        'is_active',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $word): string => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)->withTimestamps();
    }

    public function bulkMovements(): HasMany
    {
        return $this->hasMany(BulkMovement::class);
    }

    public function packageMovements(): HasMany
    {
        return $this->hasMany(PackageMovement::class);
    }

    public function customerBulkMovements(): HasMany
    {
        return $this->hasMany(BulkMovement::class, 'customer_id');
    }

    public function customerPackageMovements(): HasMany
    {
        return $this->hasMany(PackageMovement::class, 'customer_id');
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isStaff(): bool
    {
        return $this->is_staff;
    }

    public function isCustomer(): bool
    {
        return ! $this->is_admin && ! $this->is_staff;
    }
}
