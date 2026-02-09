<?php

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageStock extends Model
{
    /** @use HasFactory<\Database\Factories\PackageStockFactory> */
    use HasFactory, TracksActivity;

    protected $fillable = [
        'location_id',
        'product_id',
        'package_size_id',
        'quantity',
        'price',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packageSize(): BelongsTo
    {
        return $this->belongsTo(PackageSize::class);
    }

    public function packageMovements(): HasMany
    {
        return $this->hasMany(PackageMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }
}
