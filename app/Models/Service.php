<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'duration_minutes',
        'image_url',
        'included_features',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'duration_minutes' => 'integer',
        'included_features' => 'json',
    ];

    /**
     * Get all bookings for this service
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all active bookings through bookings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get bookings for repaint services
     */
    public function scopeRepaint($query)
    {
        return $query->where('type', 'repaint');
    }

    /**
     * Get bookings for general services
     */
    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }

    /**
     * Format price as currency
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price / 100, 0, ',', '.');
    }
}
