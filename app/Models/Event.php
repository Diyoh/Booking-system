<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Event Model
 * 
 * Represents event resources available for ticket-based booking.
 * Events are booked by quantity (number of tickets).
 * 
 * Relationships:
 * - bookings: One-to-many (polymorphic through bookings.resource_type = 'event')
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $event_date
 * @property string $start_time
 * @property string $end_time
 * @property string $location
 * @property float $ticket_price
 * @property int $available_slots
 * @property int $booked_slots
 * @property string|null $image_url
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Event extends Model
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
        'event_date',
        'start_time',
        'end_time',
        'location',
        'ticket_price',
        'available_slots',
        'booked_slots',
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
            'event_date' => 'date',
            'is_active' => 'boolean',
            'ticket_price' => 'decimal:2',
            'available_slots' => 'integer',
            'booked_slots' => 'integer',
        ];
    }

    /**
     * Get all bookings for this event.
     * Note: This is a polymorphic relationship through the bookings table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookings()
    {
        return $this->morphMany(Booking::class, 'resource');
    }

    /**
     * Scope query to only active events.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only upcoming events (future dates).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', Carbon::today());
    }

    /**
     * Scope query to events with available slots.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->whereColumn('booked_slots', '<', 'available_slots');
    }

    /**
     * Check if event has available slots for booking.
     *
     * @param int $quantity Requested number of tickets
     * @return bool
     */
    public function hasAvailableSlots(int $quantity = 1): bool
    {
        return ($this->available_slots - $this->booked_slots) >= $quantity;
    }

    /**
     * Get number of remaining slots.
     *
     * @return int
     */
    public function getRemainingSlotsAttribute(): int
    {
        return max(0, $this->available_slots - $this->booked_slots);
    }

    /**
     * Increment booked slots when a booking is confirmed.
     * This is typically called from BookingService.
     *
     * @param int $quantity
     * @return bool
     */
    public function incrementBookedSlots(int $quantity): bool
    {
        if (!$this->hasAvailableSlots($quantity)) {
            return false;
        }

        $this->increment('booked_slots', $quantity);
        return true;
    }

    /**
     * Decrement booked slots when a booking is cancelled.
     *
     * @param int $quantity
     * @return void
     */
    public function decrementBookedSlots(int $quantity): void
    {
        $this->decrement('booked_slots', $quantity);
    }

    /**
     * Get formatted price for display.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'FCFA ' . number_format($this->ticket_price, 2);
    }

    /**
     * Check if event is sold out.
     *
     * @return bool
     */
    public function isSoldOut(): bool
    {
        return $this->booked_slots >= $this->available_slots;
    }

    /**
     * Get formatted event date and time for display.
     *
     * @return string
     */
    public function getFormattedDateTimeAttribute(): string
    {
        return $this->event_date->format('M d, Y') . ' at ' . 
               Carbon::parse($this->start_time)->format('g:i A');
    }
}
