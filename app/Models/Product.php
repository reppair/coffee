<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Models\Concerns\TracksActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Product extends Model implements Sortable
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes, SortableTrait, TracksActivity;

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug ??= Str::slug($product->name);
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'slug',
        'type',
        'sku',
        'image',
        'is_active',
        'category_sort_order',
    ];

    public array $sortable = [
        'order_column_name' => 'category_sort_order',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bulkStocks(): HasMany
    {
        return $this->hasMany(BulkStock::class);
    }

    public function packageStocks(): HasMany
    {
        return $this->hasMany(PackageStock::class);
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('category_id', $this->category_id);
    }
}
