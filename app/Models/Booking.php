<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesReferenceCode;
use Carbon\Carbon;

/**
 * Booking Model
 * 
 * Central model managing all resource reservations.
 * Implements polymorphic relationship to support both Hall and Event bookings.
 * 
 * Critical functionality:
 * - Reservation hold system (5-minute grace period)
 * - Reference code generation for offline verification
 * - Status lifecycle management (pending -> confirmed/cancelled/expired)
 * 
 * Relationships:
 * - user: Belongs to User
 * - resource: Polymorphic (Hall or Event)
 * - transaction: Has one Transaction
 * 
 * @property int $id
 * @property int $user_id
 * @property string $resource_type
 * @property int $resource_id
 * @property string $booking_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int $quantity
 * @property float $total_amount
 * @property string $status
 * @property string $reference_code
 * @property \Illuminate\Support\Carbon|null $hold_expires_at
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property string $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Booking extends Model
{
    use HasFactory, GeneratesReferenceCode;

    /**
     * Booking status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Resource type constants.
     */
    const TYPE_HALL = 'hall';
    const TYPE_EVENT = 'event';

    /**
     * Source constants.
     */
    const SOURCE_WEB = 'web';
    const SOURCE_USSD = 'ussd';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'resource_type',
        'resource_id',
        'booking_date',
        'start_time',
        'end_time',
        'quantity',
        'total_amount',
        'status',
        'reference_code',
        'hold_expires_at',
        'confirmed_at',
        'source',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'hold_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Boot method to handle model events.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate reference code on creation
        static::creating(function ($booking) {
            if (empty($booking->reference_code)) {
                $booking->reference_code = $booking->generateReferenceCode();
            }

            // Set hold expiry time (5 minutes from now)
            if ($booking->status === self::STATUS_PENDING) {
                $booking->hold_expires_at = Carbon::now()->addMinutes(config('app.booking_hold_minutes', 5));
            }
        });
    }

    /**
     * Get the user who made this booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the resource (Hall or Event) for this booking.
     * Polymorphic relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function resource()
    {
        return $this->morphTo();
    }

    /**
     * Get the transaction associated with this booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Scope query to only pending bookings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to only confirmed bookings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope query to expired hold bookings.
     * Used by cron job to clean up stale reservations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiredHolds($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('hold_expires_at', '<', Carbon::now());
    }

    /**
     * Scope query for a specific user's bookings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope query for hall bookings only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHalls($query)
    {
        return $query->where('resource_type', self::TYPE_HALL);
    }

    /**
     * Scope query for event bookings only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEvents($query)
    {
        return $query->where('resource_type', self::TYPE_EVENT);
    }

    /**
     * Confirm the booking after successful payment.
     *
     * @return bool
     */
    public function confirm(): bool
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->confirmed_at = Carbon::now();
        $this->hold_expires_at = null;  // Clear hold expiry

        return $this->save();
    }

    /**
     * Cancel the booking.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Mark booking as expired (hold timeout).
     *
     * @return bool
     */
    public function markAsExpired(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        return $this->save();
    }

    /**
     * Check if booking hold has expired.
     *
     * @return bool
     */
    public function isHoldExpired(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               $this->hold_expires_at &&
               Carbon::now()->greaterThan($this->hold_expires_at);
    }

    /**
     * Check if booking is confirmed.
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if booking is pending payment.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get formatted total amount for display.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get human-readable booking details for SMS/display.
     *
     * @return string
     */
    public function getBookingDetailsAttribute(): string
    {
        $details = "{$this->resource->name} on {$this->booking_date->format('M d, Y')}";
        
        if ($this->resource_type === self::TYPE_HALL) {
            $details .= " from {$this->start_time} to {$this->end_time}";
        } else {
            $details .= " - {$this->quantity} ticket(s)";
        }

        return $details;
    }
}
