<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'brand',
        'model',
        'year',
        'color',
        'license_plate',
        'description',
        'price',
        'mileage',
        'fuel_type',
        'transmission',
        'images',
        'features',
        'status',
        'views_count',
        'listed_at',
        'sold_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'price' => 'integer',
        'mileage' => 'integer',
        'views_count' => 'integer',
        'listed_at' => 'datetime',
        'sold_at' => 'datetime',
        'images' => 'json',
        'features' => 'json',
    ];

    /**
     * Get the seller of the car
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Scope to get available cars
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to search cars
     */
    public function scopeSearch($query, ?string $keyword = null)
    {
        if (!$keyword) {
            return $query;
        }

        // Handle null inputs for garage search edge case
        return $query->where(function ($q) use ($keyword) {
            $q->where('brand', 'like', "%{$keyword}%")
              ->orWhere('model', 'like', "%{$keyword}%")
              ->orWhere('color', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope to filter by price range
     */
    public function scopePriceRange($query, ?int $minPrice = null, ?int $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope to filter by year
     */
    public function scopeYear($query, ?int $year = null)
    {
        if ($year) {
            return $query->where('year', $year);
        }

        return $query;
    }

    /**
     * Scope to filter by fuel type
     */
    public function scopeFuelType($query, ?string $fuelType = null)
    {
        if ($fuelType) {
            return $query->where('fuel_type', $fuelType);
        }

        return $query;
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('views_count');
    }

    /**
     * Mark as sold
     */
    public function markAsSold(): void
    {
        $this->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);
    }

    /**
     * Format price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price / 100, 0, ',', '.');
    }

    /**
     * Get main image (first image in array)
     */
    public function getMainImageAttribute(): ?string
    {
        return $this->images ? $this->images[0] : null;
    }
}
