<?php

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageSize extends Model
{
    /** @use HasFactory<\Database\Factories\PackageSizeFactory> */
    use HasFactory, TracksActivity;

    protected $fillable = [
        'name',
        'weight_grams',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function packageStocks(): HasMany
    {
        return $this->hasMany(PackageStock::class);
    }
}
