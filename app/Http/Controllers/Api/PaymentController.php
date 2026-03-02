<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PaymentController
 * 
 * Handles payment callbacks from Africa's Talking.
 * This webhook is called asynchronously when a payment succeeds/fails.
 * 
 * Callback Flow:
 * 1. User completes STK Push on their phone
 * 2. Africa's Talking processes payment
 * 3. Africa's Talking POSTs to this callback URL
 * 4. We update transaction and booking status
 * 5. Send SMS confirmation to user
 * 
 * Callback URL: {NGROK_URL}/api/payment/callback
 * 
 * @see https://developers.africastalking.com/docs/payments/mobile/b2c
 */
class PaymentController extends Controller
{
    /**
     * Payment Service instance.
     *
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * Constructor - Inject PaymentService.
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle payment callback from Africa's Talking.
     * 
     * This endpoint is called when payment status changes.
     * The callback includes transaction details and status.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        // Get all callback data
        $callbackData = $request->all();

        // Log the callback
        Log::info('Payment Callback Received', [
            'data' => $callbackData,
            'ip' => $request->ip(),
        ]);

        try {
            // Process the callback
            $result = $this->paymentService->handleCallback($callbackData);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment processed successfully',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment processing failed',
                ], 200);  // Still 200 to acknowledge receipt
            }

        } catch (\Exception $e) {
            // Log error but still return 200 to Africa's Talking
            Log::error('Payment Callback Error', [
                'error' => $e->getMessage(),
                'data' => $callbackData,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing callback',
            ], 200);
        }
    }

    /**
     * Manual payment status check endpoint (for debugging).
     * 
     * Allows admin to manually query transaction status.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $transactionId = $request->input('transaction_id');

        try {
            $transaction = \App\Models\Transaction::find($transactionId);

            if (!$transaction) {
                return response()->json([
                    'error' => 'Transaction not found',
                ], 404);
            }

            $status = $this->paymentService->checkTransactionStatus($transaction);

            return response()->json([
                'transaction' => $transaction,
                'status_check' => $status,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
