<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Hall Model
 * 
 * Represents community hall resources available for time-based booking.
 * Halls are booked by hour with start_time and end_time.
 * 
 * Relationships:
 * - bookings: One-to-many (polymorphic through bookings.resource_type = 'hall')
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $location
 * @property int $capacity
 * @property float $priceperper_hour
 * @property array|null $amenities
 * @property string|null $image_url
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Hall extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'location',
        'capacity',
        'price_per_hour',
        'amenities',
        'image_url',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amenities' => 'array',  // Automatically decode JSON to array
            'is_active' => 'boolean',
            'price_per_hour' => 'decimal:2',
        ];
    }

    /**
     * Get all bookings for this hall.
     * Note: This is a polymorphic relationship through the bookings table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookings()
    {
        return $this->morphMany(Booking::class, 'resource');
    }

    /**
     * Scope query to only active halls.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to filter by minimum capacity.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $capacity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMinCapacity($query, int $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }

    /**
     * Scope query to filter by maximum price per hour.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $maxPrice
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMaxPrice($query, float $maxPrice)
    {
        return $query->where('price_per_hour', '<=', $maxPrice);
    }

    /**
     * Get formatted price for display.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_per_hour, 2);
    }

    /**
     * Get amenities as comma-separated string for USSD display.
     *
     * @return string
     */
    public function getAmenitiesStringAttribute(): string
    {
        return is_array($this->amenities) ? implode(', ', $this->amenities) : '';
    }
}
