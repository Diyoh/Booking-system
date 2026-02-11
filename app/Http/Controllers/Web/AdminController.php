<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\Event;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * AdminController
 * 
 * Handles admin dashboard and resource management.
 * 
 * Features:
 * - Dashboard with statistics
 * - Hall CRUD operations
 * - Event CRUD operations
 * - Booking management
 * - User management
 */
class AdminController extends Controller
{
    /**
     * Constructor - Require auth and admin middleware.
     */
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Show admin dashboard with analytics.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_halls' => Hall::count(),
            'total_events' => Event::count(),
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::confirmed()->count(),
            'pending_bookings' => Booking::pending()->count(),
            'total_revenue' => Booking::confirmed()->sum('total_amount'),
        ];

        $recentBookings = Booking::with(['user', 'resource'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }

    /**
     * Hall management - List all halls.
     *
     * @return \Illuminate\View\View
     */
    public function halls()
    {
        $halls = Hall::orderBy('name')->paginate(20);
        
        return view('admin.halls.index', compact('halls'));
    }

    /**
     * Hall management - Create form.
     *
     * @return \Illuminate\View\View
     */
    public function createHall()
    {
        return view('admin.halls.create');
    }

    /**
     * Hall management - Store new hall.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeHall(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'array'],
            'image_url' => ['nullable', 'string'],
        ]);

        Hall::create($validated);

        return redirect()->route('admin.halls')
            ->with('success', 'Hall created successfully.');
    }

    /**
     * Event management - List all events.
     *
     * @return \Illuminate\View\View
     */
    public function events()
    {
        $events = Event::orderBy('event_date', 'desc')->paginate(20);
        
        return view('admin.events.index', compact('events'));
    }

    /**
     * Event management - Create form.
     *
     * @return \Illuminate\View\View
     */
    public function createEvent()
    {
        return view('admin.events.create');
    }

    /**
     * Event management - Store new event.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
            'ticket_price' => ['required', 'numeric', 'min:0'],
            'available_slots' => ['required', 'integer', 'min:1'],
            'image_url' => ['nullable', 'string'],
        ]);

        Event::create($validated);

        return redirect()->route('admin.events')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Booking management - List all bookings.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function bookings(Request $request)
    {
        $bookings = Booking::with(['user', 'resource'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(50);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * User management - List all users.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        $users = User::withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.users.index', compact('users'));
    }
}
