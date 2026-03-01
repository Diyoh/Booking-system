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
     * Hall management - Edit form.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editHall($id)
    {
        $hall = Hall::findOrFail($id);
        return view('admin.halls.edit', compact('hall'));
    }

    /**
     * Hall management - Update hall.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateHall(Request $request, $id)
    {
        $hall = Hall::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'array'],
            'image_url' => ['nullable', 'string'],
        ]);

        $hall->update($validated);

        return redirect()->route('admin.halls')
            ->with('success', 'Hall updated successfully.');
    }

    /**
     * Hall management - Delete hall.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyHall($id)
    {
        $hall = Hall::findOrFail($id);
        $hall->delete();

        return redirect()->route('admin.halls')
            ->with('success', 'Hall deleted successfully.');
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
     * Event management - Edit form.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editEvent($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Event management - Update event.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i:s,H:i'],
            'end_time' => ['required', 'date_format:H:i:s,H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
            'ticket_price' => ['required', 'numeric', 'min:0'],
            'available_slots' => ['required', 'integer', 'min:1'],
            'image_url' => ['nullable', 'string'],
        ]);

        $event->update($validated);

        return redirect()->route('admin.events')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Event management - Delete event.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('admin.events')
            ->with('success', 'Event deleted successfully.');
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
     * Booking management - Edit form.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editBooking($id)
    {
        $booking = Booking::with(['user', 'resource'])->findOrFail($id);
        return view('admin.bookings.edit', compact('booking'));
    }

    /**
     * Booking management - Update booking.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $booking->update($validated);

        return redirect()->route('admin.bookings')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Booking management - Delete booking.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->route('admin.bookings')
            ->with('success', 'Booking deleted successfully.');
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

    /**
     * User management - Edit form.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * User management - Update user.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:user,admin'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * User management - Delete user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->withErrors(['error' => 'You cannot delete yourself.']);
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }
}
