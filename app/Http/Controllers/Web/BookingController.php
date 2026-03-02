<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\Event;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * BookingController
 * 
 * Handles booking creation and management for the web interface.
 * 
 * Flow:
 * 1. User selects resource and date/time
 * 2. System checks availability
 * 3. Creates pending booking
 * 4. Initiates payment (STK Push)
 * 5. User completes payment on phone
 * 6. Callback confirms booking
 * 7. SMS confirmation sent
 */
class BookingController extends Controller
{
    /**
     * Service instances.
     */
    protected $bookingService;
    protected $paymentService;

    /**
     * Constructor - Inject dependencies.
     */
    public function __construct(BookingService $bookingService, PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show booking form for a hall.
     *
     * @param int $hallId
     * @return \Illuminate\View\View
     */
    public function createHallBooking($hallId)
    {
        $hall = Hall::active()->findOrFail($hallId);
        
        return view('bookings.create-hall', compact('hall'));
    }

    /**
     * Show booking form for an event.
     *
     * @param int $eventId
     * @return \Illuminate\View\View
     */
    public function createEventBooking($eventId)
    {
        $event = Event::active()->upcoming()->findOrFail($eventId);
        
        return view('bookings.create-event', compact('event'));
    }

    /**
     * Store hall booking.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeHallBooking(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'hall_id' => ['required', 'exists:halls,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create booking
            $booking = $this->bookingService->createBooking(
                Auth::user(),
                Booking::TYPE_HALL,
                $request->hall_id,
                [
                    'booking_date' => $request->booking_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'source' => Booking::SOURCE_WEB,
                ]
            );

            // Initiate payment
            $this->paymentService->initiatePayment($booking);

            return redirect()->route('bookings.confirmation', $booking->id)
                ->with('success', 'Booking created! Please check your phone for payment prompt.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Store event booking.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEventBooking(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'exists:events,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get event and check availability
            $event = Event::findOrFail($request->event_id);
            
            if (!$event->hasAvailableSlots($request->quantity)) {
                return redirect()->back()
                    ->withErrors(['error' => 'Not enough tickets available.'])
                    ->withInput();
            }

            // Create booking
            $booking = $this->bookingService->createBooking(
                Auth::user(),
                Booking::TYPE_EVENT,
                $request->event_id,
                [
                    'booking_date' => $event->event_date,
                    'quantity' => $request->quantity,
                    'source' => Booking::SOURCE_WEB,
                ]
            );

            // Initiate payment
            $this->paymentService->initiatePayment($booking);

            return redirect()->route('bookings.confirmation', $booking->id)
                ->with('success', 'Booking created! Please check your phone for payment prompt.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show booking confirmation page.
     *
     * @param int $bookingId
     * @return \Illuminate\View\View
     */
    public function confirmation($bookingId)
    {
        $booking = Booking::with(['resource', 'transaction'])
            ->where('user_id', Auth::id())
            ->findOrFail($bookingId);

        return view('bookings.confirmation', compact('booking'));
    }

    /**
     * Check availability for a hall (AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkHallAvailability(Request $request)
    {
        $available = $this->bookingService->isHallAvailable(
            $request->hall_id,
            $request->date,
            $request->start_time,
            $request->end_time
        );

        return response()->json(['available' => $available]);
    }

    /**
     * Check availability for an event (AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEventAvailability(Request $request)
    {
        $available = $this->bookingService->isEventAvailable(
            $request->event_id,
            $request->quantity
        );

        return response()->json(['available' => $available]);
    }

    /**
     * Cancel a booking.
     *
     * @param int $bookingId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($bookingId)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($bookingId);

        if (!$booking->isConfirmed()) {
            return redirect()->back()
                ->withErrors(['error' => 'Only confirmed bookings can be cancelled.']);
        }

        try {
            $this->bookingService->cancelBooking($booking);

            return redirect()->route('dashboard.bookings')
                ->with('success', 'Booking cancelled successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
