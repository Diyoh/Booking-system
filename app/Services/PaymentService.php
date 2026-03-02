<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use AfricasTalking\SDK\AfricasTalking;

/**
 * PaymentService
 * 
 * Handles all payment operations via Africa's Talking Payment API.
 * Implements STK Push (SIM Toolkit Push) for mobile money payments.
 * 
 * Flow:
 * 1. User confirms booking → createBooking() → PaymentService.initiatePayment()
 * 2. Africa's Talking triggers STK Push to user's phone
 * 3. User enters PIN on phone
 * 4. Africa's Talking sends callback to /api/payment/callback
 * 5. PaymentController → PaymentService.handleCallback() → confirm booking
 * 
 * Integration:
 * - Uses Africa's Talking PHP SDK
 * - Sandbox mode for development/testing
 * - Stores transaction records for audit trail
 * 
 * @see https://developers.africastalking.com/docs/payments/overview
 */
class PaymentService
{
    /**
     * Africa's Talking SDK instance.
     *
     * @var AfricasTalking
     */
    protected $at;

    /**
     * Payment service instance from SDK.
     *
     * @var \AfricasTalking\SDK\Payments
     */
    protected $payment;

    /**
     * Constructor - Initialize Africa's Talking SDK.
     */
    public function __construct()
    {
        $this->at = new AfricasTalking(
            config('services.africastalking.username'),
            config('services.africastalking.api_key')
        );

// $this->payment = $this->at->payments; // Payments not supported in this SDK version
    }

    /**
     * Initiate mobile money payment (STK Push).
     * 
     * This triggers a payment prompt on the user's phone.
     * The user must enter their M-Pesa/Mobile Money PIN to authorize.
     * 
     * The booking remains in 'pending' status until callback confirms success.
     *
     * @param Booking $booking
     * @return Transaction
     * @throws \Exception
     */
    public function initiatePayment(Booking $booking): Transaction
    {
        // Create transaction record with pending status
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'phone_number' => $booking->user->phone_number,
            'amount' => $booking->total_amount,
            'status' => Transaction::STATUS_PENDING,
        ]);

        try {
            // Prepare payment request
            $productName = config('app.name');
            $currencyCode = config('services.africastalking.currency_code', 'XAF');
            
            // Call Africa's Talking Mobile Checkout API
            // $response = $this->payment->mobileCheckout([
            //     'productName' => $productName,
            //     'phoneNumber' => $booking->user->phone_number,
            //     'currencyCode' => $currencyCode,
            //     'amount' => $booking->total_amount,
            //     'metadata' => [
            //         'booking_id' => $booking->id,
            //         'reference_code' => $booking->reference_code,
            //     ],
            // ]);

            // SIMULATION: Fake response
            $response = [
                'status' => 'PendingConfirmation',
                'description' => 'The service request is processed successfully.',
                'transactionId' => 'ATPid_' . uniqid(),
                'providerChannel' => '525900'
            ];

            // Log the request
            Log::info('Payment initiated', [
                'booking_id' => $booking->id,
                'transaction_id' => $transaction->id,
                'phone_number' => $booking->user->phone_number,
                'amount' => $booking->total_amount,
                'response' => $response,
            ]);

            // Store Africa's Talking response
            $transaction->provider_response = $response;
            $transaction->save();

            return $transaction;

        } catch (\Exception $e) {
            // Mark transaction as failed
            $transaction->markAsFailed(['error' => $e->getMessage()]);

            Log::error('Payment initiation failed', [
                'booking_id' => $booking->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle payment callback from Africa's Talking.
     * 
     * This method is called by the PaymentController when Africa's Talking
     * sends a notification about payment status (success/failure).
     * 
     * Based on the status, we either:
     * - Confirm the booking (success)
     * - Mark transaction as failed and let booking expire (failure)
     *
     * @param array $callbackData Payload from Africa's Talking
     * @return bool
     */
    public function handleCallback(array $callbackData): bool
    {
        try {
            // Extract callback data
            $status = $callbackData['status'] ?? null;
            $transactionId = $callbackData['transactionId'] ?? null;
            $phoneNumber = $callbackData['phoneNumber'] ?? null;

            // Find the transaction
            // Note: Africa's Talking may not return our transaction ID directly,
            // so we search by phone number and amount, or use metadata if available
            $transaction = $this->findTransactionFromCallback($callbackData);

            if (!$transaction) {
                Log::warning('Transaction not found for callback', ['callback' => $callbackData]);
                return false;
            }

            // Store full callback response
            $transaction->provider_response = $callbackData;
            $transaction->save();

            // Check payment status
            if ($status === 'Success' || $status === 'Completed') {
                // Payment successful
                $transaction->markAsSuccessful($transactionId, $callbackData);

                // Confirm the booking
                $booking = $transaction->booking;
                app(BookingService::class)->confirmBooking($booking);

                // Send SMS confirmation
                app(SmsService::class)->sendBookingConfirmation($booking);

                Log::info('Payment callback processed - Success', [
                    'transaction_id' => $transaction->id,
                    'booking_id' => $booking->id,
                ]);

                return true;

            } else {
                // Payment failed or cancelled
                $transaction->markAsFailed($callbackData);

                Log::info('Payment callback processed - Failed', [
                    'transaction_id' => $transaction->id,
                    'status' => $status,
                ]);

                return false;
            }

        } catch (\Exception $e) {
            Log::error('Payment callback handling failed', [
                'error' => $e->getMessage(),
                'callback' => $callbackData,
            ]);

            return false;
        }
    }

    /**
     * Find transaction from callback data.
     * 
     * Africa's Talking callbacks include various identifiers.
     * We try to match using metadata (booking_id) or phone + amount.
     *
     * @param array $callbackData
     * @return Transaction|null
     */
    protected function findTransactionFromCallback(array $callbackData): ?Transaction
    {
        // Try to find by metadata (if Africa's Talking returns it)
        if (isset($callbackData['metadata']['booking_id'])) {
            $bookingId = $callbackData['metadata']['booking_id'];
            return Transaction::where('booking_id', $bookingId)
                ->where('status', Transaction::STATUS_PENDING)
                ->first();
        }

        // Fallback for Simulation: Use clientAccount as booking_id
        if (isset($callbackData['clientAccount'])) {
            $bookingId = $callbackData['clientAccount'];
            // Check if clientAccount is numeric (Booking ID)
            if (is_numeric($bookingId)) {
                return Transaction::where('booking_id', $bookingId)
                    ->where('status', Transaction::STATUS_PENDING)
                    ->first();
            }
        }

        // Fallback: Find by phone number and amount (recent pending transaction)
        $phoneNumber = $callbackData['phoneNumber'] ?? null;
        $amount = $callbackData['value'] ?? null;

        if ($phoneNumber && $amount) {
            return Transaction::where('phone_number', $phoneNumber)
                ->where('amount', $amount)
                ->where('status', Transaction::STATUS_PENDING)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return null;
    }

    /**
     * Check transaction status manually (for debugging/manual reconciliation).
     *
     * @param Transaction $transaction
     * @return array|null
     */
    public function checkTransactionStatus(Transaction $transaction): ?array
    {
        try {
            // Africa's Talking doesn't have a direct status check API in sandbox
            // In production, you'd query their transaction status endpoint
            // For now, return null or implement custom logic

            Log::info('Manual transaction status check', [
                'transaction_id' => $transaction->id,
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Transaction status check failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
