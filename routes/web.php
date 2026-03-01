<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected user routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/halls', [DashboardController::class, 'halls'])->name('halls.index');
    Route::get('/halls/{id}', [DashboardController::class, 'showHall'])->name('halls.show');
    Route::get('/events', [DashboardController::class, 'events'])->name('events.index');
    Route::get('/events/{id}', [DashboardController::class, 'showEvent'])->name('events.show');
    Route::get('/my-bookings', [DashboardController::class, 'bookings'])->name('dashboard.bookings');

    // Booking routes
    Route::get('/bookings/hall/{hallId}/create', [BookingController::class, 'createHallBooking'])->name('bookings.create.hall');
    Route::post('/bookings/hall', [BookingController::class, 'storeHallBooking'])->name('bookings.store.hall');
    Route::get('/bookings/event/{eventId}/create', [BookingController::class, 'createEventBooking'])->name('bookings.create.event');
    Route::post('/bookings/event', [BookingController::class, 'storeEventBooking'])->name('bookings.store.event');
    Route::get('/bookings/{id}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
    Route::delete('/bookings/{id}', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // AJAX availability checking
    Route::post('/api/check-hall-availability', [BookingController::class, 'checkHallAvailability'])->name('api.check-hall');
    Route::post('/api/check-event-availability', [BookingController::class, 'checkEventAvailability'])->name('api.check-event');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Hall management
    Route::get('/halls', [AdminController::class, 'halls'])->name('halls');
    Route::get('/halls/create', [AdminController::class, 'createHall'])->name('halls.create');
    Route::post('/halls', [AdminController::class, 'storeHall'])->name('halls.store');
    Route::get('/halls/{id}/edit', [AdminController::class, 'editHall'])->name('halls.edit');
    Route::put('/halls/{id}', [AdminController::class, 'updateHall'])->name('halls.update');
    Route::delete('/halls/{id}', [AdminController::class, 'destroyHall'])->name('halls.destroy');
    
    // Event management
    Route::get('/events', [AdminController::class, 'events'])->name('events');
    Route::get('/events/create', [AdminController::class, 'createEvent'])->name('events.create');
    Route::post('/events', [AdminController::class, 'storeEvent'])->name('events.store');
    Route::get('/events/{id}/edit', [AdminController::class, 'editEvent'])->name('events.edit');
    Route::put('/events/{id}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::delete('/events/{id}', [AdminController::class, 'destroyEvent'])->name('events.destroy');
    
    // Booking management
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{id}/edit', [AdminController::class, 'editBooking'])->name('bookings.edit');
    Route::put('/bookings/{id}', [AdminController::class, 'updateBooking'])->name('bookings.update');
    Route::delete('/bookings/{id}', [AdminController::class, 'destroyBooking'])->name('bookings.destroy');
    
    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
});
