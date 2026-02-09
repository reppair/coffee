<?php

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory, TracksActivity;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function bulkStocks(): HasMany
    {
        return $this->hasMany(BulkStock::class);
    }

    public function packageStocks(): HasMany
    {
        return $this->hasMany(PackageStock::class);
    }

    public function bulkMovements(): HasMany
    {
        return $this->hasMany(BulkMovement::class);
    }

    public function packageMovements(): HasMany
    {
        return $this->hasMany(PackageMovement::class);
    }
}
