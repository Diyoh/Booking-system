<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use AfricasTalking\SDK\AfricasTalking;

/**
 * SmsService
 * 
 * Handles SMS notifications via Africa's Talking SMS API.
 * Used for sending booking confirmations, payment receipts,
 * and booking history to users.
 * 
 * SMS Templates:
 * - Booking confirmation with reference code
 * - Payment receipt
 * - Booking history (for USSDusers)
 * 
 * Integration:
 * - Uses Africa's Talking PHP SDK
 * - Logs all SMS for audit trail
 */
class SmsService
{
    /**
     * Africa's Talking SDK instance.
     *
     * @var AfricasTalking
     */
    protected $at;

    /**
     * SMS service instance from SDK.
     *
     * @var \AfricasTalking\SDK\SMS
     */
    protected $sms;

    /**
     * Constructor - Initialize Africa's Talking SDK.
     */
    public function __construct()
    {
        $this->at = new AfricasTalking(
            config('services.africastalking.username'),
            config('services.africastalking.api_key')
        );

        $this->sms = $this->at->sms();
    }

    /**
     * Send booking confirmation SMS.
     * 
     * Template: "Your booking for [Resource] on [Date] is confirmed. 
     *            Ref: [CODE]. Show this at the venue. Total: $[Amount]"
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendBookingConfirmation(Booking $booking): bool
    {
        $message = sprintf(
            "Your booking for %s on %s is confirmed. Ref: %s. Show this at the venue. Total: %s",
            $booking->resource->name,
            $booking->booking_date->format('M d, Y'),
            $booking->reference_code,
            $booking->formatted_amount
        );

        return $this->send($booking->user->phone_number, $message);
    }

    /**
     * Send payment receipt SMS.
     * 
     * Template: "Payment of $[Amount] received for booking [Ref]. 
     *            Transaction ID: [TransactionID]"
     *
     * @param Booking $booking
     * @return bool
     */
    public function sendPaymentReceipt(Booking $booking): bool
    {
        $transaction = $booking->transaction;

        $message = sprintf(
            "Payment of %s received for booking %s. Transaction ID: %s. Thank you!",
            $booking->formatted_amount,
            $booking->reference_code,
            $transaction->provider_transaction_id ?? 'N/A'
        );

        return $this->send($booking->user->phone_number, $message);
    }

    /**
     * Send booking history SMS (for USSD users).
     * 
     * Template: "Your bookings:\n
     *            1. [Ref] - [Resource] - [Status]\n
     *            2. [Ref] - [Resource] - [Status]..."
     *
     * @param User $user
     * @return bool
     */
    public function sendBookingHistory(User $user): bool
    {
        $bookings = app(BookingService::class)->getUserBookings($user, null, 5);

        if ($bookings->isEmpty()) {
            $message = "You have no bookings yet. Dial *384*10# to make a booking.";
        } else {
            $message = "Your recent bookings:\n";
            
            foreach ($bookings as $index => $booking) {
                $message .= sprintf(
                    "%d. %s - %s - %s\n",
                    $index + 1,
                    $booking->reference_code,
                    $booking->resource->name,
                    ucfirst($booking->status)
                );
            }
        }

        return $this->send($user->phone_number, $message);
    }

    /**
     * Send a custom SMS message.
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function send(string $phoneNumber, string $message): bool
    {
        try {
            // Send SMS via Africa's Talking
            $result = $this->sms->send([
                'to' => $phoneNumber,
                'message' => $message,
                'from' => config('services.africastalking.sender_id'),
            ]);

            Log::info('SMS sent', [
                'phone' => $phoneNumber,
                'message' => $message,
                'result' => $result,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send bulk SMS (for announcements, reminders, etc.).
     *
     * @param array $phoneNumbers
     * @param string $message
     * @return array Results from Africa's Talking
     */
    public function sendBulk(array $phoneNumbers, string $message): array
    {
        try {
            $result = $this->sms->send([
                'to' => $phoneNumbers,
                'message' => $message,
                'from' => config('services.africastalking.sender_id'),
            ]);

            Log::info('Bulk SMS sent', [
                'recipients' => count($phoneNumbers),
                'result' => $result,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Bulk SMS sending failed', [
                'recipients' => count($phoneNumbers),
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }
}
