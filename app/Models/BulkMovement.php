<?php

namespace App\Models;

use App\Enums\BulkMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BulkMovement extends Model
{
    /** @use HasFactory<\Database\Factories\BulkMovementFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'location_id',
        'bulk_stock_id',
        'user_id',
        'customer_id',
        'type',
        'quantity_grams_change',
        'quantity_grams_before',
        'quantity_grams_after',
        'cost_per_kg',
        'sale_price_per_kg',
        'supplier',
        'related_movement_id',
        'package_movement_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => BulkMovementType::class,
            'cost_per_kg' => 'decimal:2',
            'sale_price_per_kg' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function bulkStock(): BelongsTo
    {
        return $this->belongsTo(BulkStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function relatedMovement(): BelongsTo
    {
        return $this->belongsTo(BulkMovement::class, 'related_movement_id');
    }

    public function packageMovement(): BelongsTo
    {
        return $this->belongsTo(PackageMovement::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function tapActivity($activity, string $eventName): void
    {
        $activity->location_id = $this->location_id;
    }
}
