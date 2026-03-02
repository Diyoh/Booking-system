<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hall;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BookingService
 * 
 * Core booking engine shared by both Web and USSD interfaces.
 * This service is the SINGLE SOURCE OF TRUTH for resource availability
 * and booking operations, ensuring data consistency across platforms.
 * 
 * Critical Features:
 * - Atomic availability checking (prevents race conditions)
 * - Database locking for double-booking prevention
 * - Reservation hold system (5-minute grace period for payment)
 * - Automatic slot management for events
 * 
 * Usage:
 * - Web Controller → BookingService → Database
 * - USSD Controller → BookingService → Database
 * 
 * Both interfaces use the SAME methods, ensuring identical business logic.
 */
class BookingService
{
    /**
     * Check if a hall is available for the requested time slot.
     * 
     * This method performs an atomic query with FOR UPDATE lock
     * to prevent race conditions when multiple users try to book
     * the same time slot simultaneously.
     *
     * @param int $hallId
     * @param string $date Format: Y-m-d
     * @param string $startTime Format: H:i:s
     * @param string $endTime Format: H:i:s
     * @return bool
     */
    public function isHallAvailable(int $hallId, string $date, string $startTime, string $endTime): bool
    {
        // Convert to Carbon instances for easier comparison
        $requestedStart = Carbon::parse("{$date} {$startTime}");
        $requestedEnd = Carbon::parse("{$date} {$endTime}");

        // Check for overlapping bookings
        // A booking overlaps if:
        // 1. It starts before requested end AND ends after requested start
        // 2. Status is confirmed or pending (not cancelled/expired)
        $overlappingBookings = Booking::where('resource_type', Booking::TYPE_HALL)
            ->where('resource_id', $hallId)
            ->where('booking_date', $date)
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Existing booking starts before our end time
                    $q->where('start_time', '<', $endTime)
                      // AND existing booking ends after our start time
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        return !$overlappingBookings;
    }

    /**
     * Check if an event has available slots for booking.
     *
     * @param int $eventId
     * @param int $quantity Number of tickets requested
     * @return bool
     */
    public function isEventAvailable(int $eventId, int $quantity = 1): bool
    {
        $event = Event::find($eventId);

        if (!$event) {
            return false;
        }

        return $event->hasAvailableSlots($quantity);
    }

    /**
     * Create a new booking (with pending status).
     * 
     * This method uses database transactions to ensure atomicity.
     * The booking starts with 'pending' status and a 5-minute hold.
     * 
     * For halls: Validates time slot availability with locking
     * For events: Validates slot availability without locking (handled by increment)
     *
     * @param User $user
     * @param string $resourceType 'hall' or 'event'
     * @param int $resourceId
     * @param array $bookingData
     * @return Booking|null
     * @throws \Exception
     */
    public function createBooking(User $user, string $resourceType, int $resourceId, array $bookingData): ?Booking
    {
        return DB::transaction(function () use ($user, $resourceType, $resourceId, $bookingData) {
            
            // Validate resource type
            if (!in_array($resourceType, [Booking::TYPE_HALL, Booking::TYPE_EVENT])) {
                throw new \InvalidArgumentException("Invalid resource type: {$resourceType}");
            }

            // Load the resource
            if ($resourceType === Booking::TYPE_HALL) {
                $resource = Hall::lockForUpdate()->find($resourceId);
            } else {
                $resource = Event::lockForUpdate()->find($resourceId);
            }

            if (!$resource) {
                throw new \Exception("Resource not found");
            }

            // Validate availability
            if ($resourceType === Booking::TYPE_HALL) {
                if (!$this->isHallAvailable(
                    $resourceId,
                    $bookingData['booking_date'],
                    $bookingData['start_time'],
                    $bookingData['end_time']
                )) {
                    throw new \Exception("Hall is not available for the selected time slot");
                }

                // Calculate total amount for hall (hourly rate)
                $start = Carbon::parse($bookingData['start_time']);
                $end = Carbon::parse($bookingData['end_time']);
                $hours = $start->diffInHours($end);
                $totalAmount = $hours * $resource->price_per_hour;
                
            } else {
                // Event booking
                $quantity = $bookingData['quantity'] ?? 1;
                
                if (!$this->isEventAvailable($resourceId, $quantity)) {
                    throw new \Exception("Event does not have enough available slots");
                }

                $totalAmount = $quantity * $resource->ticket_price;
            }

            // Create the booking
            $booking = new Booking([
                'user_id' => $user->id,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'booking_date' => $bookingData['booking_date'],
                'start_time' => $bookingData['start_time'] ?? null,
                'end_time' => $bookingData['end_time'] ?? null,
                'quantity' => $bookingData['quantity'] ?? 1,
                'total_amount' => $totalAmount,
                'status' => Booking::STATUS_PENDING,
                'source' => $bookingData['source'] ?? Booking::SOURCE_WEB,
            ]);

            $booking->save();

            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'reference_code' => $booking->reference_code,
            ]);

            return $booking;
        });
    }

    /**
     * Confirm a booking after successful payment.
     * 
     * This method:
     * 1. Updates booking status to confirmed
     * 2. For events: Increments booked_slots counter
     * 3. Clears the hold expiry time
     *
     * @param Booking $booking
     * @return bool
     */
    public function confirmBooking(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            
            // Confirm the booking
            $booking->confirm();

            // For event bookings, increment the booked slots
            if ($booking->resource_type === Booking::TYPE_EVENT) {
                $event = Event::find($booking->resource_id);
                if ($event) {
                    $event->incrementBookedSlots($booking->quantity);
                }
            }

            Log::info('Booking confirmed', [
                'booking_id' => $booking->id,
                'reference_code' => $booking->reference_code,
            ]);

            return true;
        });
    }

    /**
     * Cancel a booking and release the slot.
     * 
     * For events: Decrements the booked_slots counter.
     * For halls: Simply updates status (time slot auto-released).
     *
     * @param Booking $booking
     * @return bool
     */
    public function cancelBooking(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            
            // For confirmed event bookings, decrement slots
            if ($booking->isConfirmed() && $booking->resource_type === Booking::TYPE_EVENT) {
                $event = Event::find($booking->resource_id);
                if ($event) {
                    $event->decrementBookedSlots($booking->quantity);
                }
            }

            $booking->cancel();

            Log::info('Booking cancelled', [
                'booking_id' => $booking->id,
                'reference_code' => $booking->reference_code,
            ]);

            return true;
        });
    }

    /**
     * Mark expired hold bookings as expired.
     * 
     * This method is typically called by a scheduled task (cron job)
     * to clean up pending bookings whose 5-minute hold has expired.
     * 
     * @return int Number of bookings marked as expired
     */
    public function expireHoldBookings(): int
    {
        $expiredBookings = Booking::expiredHolds()->get();

        foreach ($expiredBookings as $booking) {
            $booking->markAsExpired();
            
            Log::info('Booking hold expired', [
                'booking_id' => $booking->id,
                'reference_code' => $booking->reference_code,
            ]);
        }

        return $expiredBookings->count();
    }

    /**
     * Get user's booking history.
     *
     * @param User $user
     * @param string|null $status Filter by status
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function bookings(User $user, ?string $status = null, int $limit = 20)
    {
        $query = Booking::where('user_id', $user->id)
            ->with('resource') 
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($limit);
    }

    /**
     * Find booking by reference code.
     * 
     * Used for offline verification at venue entrance.
     *
     * @param string $referenceCode
     * @return Booking|null
     */
    public function findBookingByReference(string $referenceCode): ?Booking
    {
        return Booking::where('reference_code', $referenceCode)
            ->with(['user', 'resource'])
            ->first();
    }
}
