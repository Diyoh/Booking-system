<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UssdMenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * UssdController
 * 
 * Gateway for all USSD requests from Africa's Talking.
 * This controller receives webhook POSTs when users dial the USSD code.
 * 
 * Africa's Talking USSD Request Format:
 * - sessionId: Unique session identifier from MNO
 * - serviceCode: USSD code dialed (e.g., *384*10#)
 * - phoneNumber: User's MSISDN (e.g., +254712345678)
 * - text: User input path (e.g., "1*2*3")
 * 
 * Response Format:
 * - CON: Continue session (show menu, wait for input)
 * - END: End session (final message)
 * 
 * Response Time: MUST be < 2 seconds to avoid MNO timeout
 * 
 * @see https://developers.africastalking.com/docs/ussd/overview
 */
class UssdController extends Controller
{
    /**
     * USSD Menu Service instance.
     *
     * @var UssdMenuService
     */
    protected $ussdService;

    /**
     * Constructor - Inject UssdMenuService.
     */
    public function __construct(UssdMenuService $ussdService)
    {
        $this->ussdService = $ussdService;
    }

    /**
     * Handle incoming USSD request.
     * 
     * This is the webhook endpoint configured in Africa's Talking.
     * URL: {NGROK_URL}/api/ussd
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        // Extract USSD parameters from Africa's Talking
        $sessionId = $request->input('sessionId');
        $phoneNumber = $request->input('phoneNumber');
        // AT sometimes sends text as null on the first request. We cast to string to prevent TypeError
        $text = (string) $request->input('text');

        // Log the incoming request
        Log::info('USSD Request Received', [
            'session_id' => $sessionId,
            'phone_number' => $phoneNumber,
            'text' => $text,
            'service_code' => $request->input('serviceCode'),
        ]);

        try {
            // STEP 1: Route to UssdMenuService which handles all menu logic and state management
            // The service returns the precise text string intended for the user's screen
            $response = $this->ussdService->handleRequest($sessionId, $phoneNumber, $text);

            // Log the response for debugging and audit purposes
            Log::info('USSD Response Sent', [
                'session_id' => $sessionId,
                'response' => $response,
            ]);

            // STEP 2: Return plain text response to Africa's Talking
            // AT requires a 200 OK with 'text/plain' content type
            // The string MUST start with 'CON ' (continue) or 'END ' (terminate session)
            return response($response, 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            // Log error
            Log::error('USSD Error', [
                'session_id' => $sessionId,
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return generic error message to user
            return response('END An error occurred. Please try again.', 200)
                ->header('Content-Type', 'text/plain');
        }
    }
}
