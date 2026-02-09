<?php

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkStock extends Model
{
    /** @use HasFactory<\Database\Factories\BulkStockFactory> */
    use HasFactory, TracksActivity;

    protected $fillable = [
        'location_id',
        'product_id',
        'quantity_grams',
        'low_stock_threshold_grams',
        'default_sale_price_per_kg',
    ];

    protected function casts(): array
    {
        return [
            'default_sale_price_per_kg' => 'decimal:2',
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

    public function bulkMovements(): HasMany
    {
        return $this->hasMany(BulkMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_grams <= $this->low_stock_threshold_grams;
    }
}
