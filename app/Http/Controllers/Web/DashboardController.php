<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardController
 * 
 * Handles the main user dashboard and resource browsing.
 * 
 * Features:
 * - Display available halls and events
 * - Filter resources by criteria
 * - Show user's booking history
 * - Quick stats for user
 */
class DashboardController extends Controller
{
    /**
     * Booking service instance.
     *
     * @var BookingService
     */
    protected $bookingService;

    /**
     * Constructor - Inject dependencies.
     */
    public function __construct(BookingService $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    /**
     * Show dashboard with available resources.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get active halls
        $halls = Hall::active()
            ->when($request->capacity_min, function ($query, $capacity) {
                return $query->minCapacity($capacity);
            })
            ->when($request->price_max, function ($query, $price) {
                return $query->maxPrice($price);
            })
            ->orderBy('name')
            ->paginate(6);

        // Get upcoming events
        $events = Event::active()
            ->upcoming()
            ->available()
            ->orderBy('event_date')
            ->take(6)
            ->get();

        // Get user's recent bookings
        $recentBookings = $this->bookingService->bookings($user, null, 5);

        // User stats
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'confirmed_bookings' => $user->bookings()->confirmed()->count(),
            'pending_bookings' => $user->bookings()->pending()->count(),
        ];

        return view('dashboard.index', compact('halls', 'events', 'recentBookings', 'stats'));
    }

    /**
     * Show all halls with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function halls(Request $request)
    {
        $halls = Hall::active()
            ->when($request->capacity_min, function ($query, $capacity) {
                return $query->where('capacity', '>=', $capacity);
            })
            ->when($request->price_max, function ($query, $price) {
                return $query->where('price_per_hour', '<=', $price);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(12);

        return view('dashboard.halls', compact('halls'));
    }

    /**
     * Show all events with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function events(Request $request)
    {
        $events = Event::active()
            ->upcoming()
            ->when($request->available_only, function ($query) {
                return $query->available();
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('event_date')
            ->paginate(12);

        return view('dashboard.events', compact('events'));
    }

    /**
     * Show single hall details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showHall($id)
    {
        $hall = Hall::active()->findOrFail($id);
        
        return view('dashboard.hall-details', compact('hall'));
    }

    /**
     * Show single event details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showEvent($id)
    {
        $event = Event::active()->findOrFail($id);
        
        return view('dashboard.event-details', compact('event'));
    }

    /**
     * Show user's booking history.
     *
     * @return \Illuminate\View\View
     */
    public function bookings()
    {
        $user = Auth::user();
        
        $bookings = $this->bookingService->bookings($user, null, 50);

        return view('dashboard.bookings', compact('bookings'));
    }
}
