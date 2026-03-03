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
        $this->middleware('auth')->except(['halls', 'events', 'showHall', 'showEvent']);
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
        $locations = \App\Models\Location::orderBy('name')->get();

        $halls = Hall::active()
            // LOCATION FILTER: Exact match on location string
            ->when($request->location, function ($query, $location) {
                return $query->where('location', $location);
            })
            // CAPACITY FILTER: Buckets based on business rules
            ->when($request->capacity, function ($query, $capacity) {
                if ($capacity === 'small') return $query->where('capacity', '<', 50);
                if ($capacity === 'medium') return $query->whereBetween('capacity', [50, 200]);
                if ($capacity === 'large') return $query->where('capacity', '>', 200);
                return $query;
            })
            ->when($request->price_range, function ($query, $price_range) {
                if ($price_range === 'low') return $query->where('price_per_hour', '<', 10000);
                if ($price_range === 'medium') return $query->whereBetween('price_per_hour', [10000, 50000]);
                if ($price_range === 'high') return $query->where('price_per_hour', '>', 50000);
                return $query;
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(12)->withQueryString();

        return view('dashboard.halls', compact('halls', 'locations'));
    }

    /**
     * Show all events with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function events(Request $request)
    {
        $locations = \App\Models\Location::orderBy('name')->get();

        $events = Event::active()
            ->upcoming()
            // LOCATION FILTER
            ->when($request->location, function ($query, $location) {
                return $query->where('location', $location);
            })
            // PRICE FILTER: Free vs Paid distinction
            ->when($request->price_range, function ($query, $price_range) {
                if ($price_range === 'free') return $query->where('ticket_price', 0);
                if ($price_range === 'paid') return $query->where('ticket_price', '>', 0);
                return $query;
            })
            // DATE FILTER: Time-based buckets relative to Carbon::now()
            ->when($request->date, function ($query, $date) {
                if ($date === 'today') return $query->whereDate('event_date', \Carbon\Carbon::today());
                if ($date === 'this_week') return $query->whereBetween('event_date', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
                if ($date === 'this_month') return $query->whereMonth('event_date', \Carbon\Carbon::now()->month)->whereYear('event_date', \Carbon\Carbon::now()->year);
                return $query;
            })
            ->when($request->available_only, function ($query) {
                return $query->available();
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy('event_date')
            ->paginate(12)->withQueryString();

        return view('dashboard.events', compact('events', 'locations'));
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
