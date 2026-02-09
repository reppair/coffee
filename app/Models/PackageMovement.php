<?php

namespace App\Models;

use App\Enums\PackageMovementType;
use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageMovement extends Model
{
    /** @use HasFactory<\Database\Factories\PackageMovementFactory> */
    use HasFactory, TracksActivity;

    protected $fillable = [
        'location_id',
        'package_stock_id',
        'user_id',
        'customer_id',
        'type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'sale_price',
        'related_movement_id',
        'bulk_movement_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PackageMovementType::class,
            'sale_price' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function packageStock(): BelongsTo
    {
        return $this->belongsTo(PackageStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function relatedMovement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_movement_id');
    }

    public function bulkMovement(): BelongsTo
    {
        return $this->belongsTo(BulkMovement::class);
    }
}
