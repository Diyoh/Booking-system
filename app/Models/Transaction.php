<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Transaction Model
 * 
 * Tracks payment transactions for bookings via Africa's Talking.
 * Stores payment status and provider responses for audit/debugging.
 * 
 * Relationships:
 * - booking: Belongs to Booking
 * 
 * @property int $id
 * @property int $booking_id
 * @property string $phone_number
 * @property float $amount
 * @property string|null $provider_transaction_id
 * @property string $status
 * @property array|null $provider_response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * Transaction status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'phone_number',
        'amount',
        'provider_transaction_id',
        'status',
        'provider_response',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_response' => 'array',  // JSON decode
        ];
    }

    /**
     * Get the booking associated with this transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope query to only pending transactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to only successful transactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Mark transaction as successful.
     *
     * @param string $providerTransactionId
     * @param array|null $response
     * @return bool
     */
    public function markAsSuccessful(string $providerTransactionId, ?array $response = null): bool
    {
        $this->status = self::STATUS_SUCCESS;
        $this->provider_transaction_id = $providerTransactionId;
        if ($response) {
            $this->provider_response = $response;
        }

        return $this->save();
    }

    /**
     * Mark transaction as failed.
     *
     * @param array|null $response
     * @return bool
     */
    public function markAsFailed(?array $response = null): bool
    {
        $this->status = self::STATUS_FAILED;
        if ($response) {
            $this->provider_response = $response;
        }

        return $this->save();
    }

    /**
     * Check if transaction is successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if transaction is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get formatted amount for display.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }
}
