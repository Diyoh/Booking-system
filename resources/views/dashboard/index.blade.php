@extends('layouts.app')

@section('title', 'Dashboard - Community Booking')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="mt-2 text-gray-600">Browse and book community halls and events</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_bookings'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Confirmed</dt>
                            <dd class="text-3xl font-semibold text-green-600">{{ $stats['confirmed_bookings'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                            <dd class="text-3xl font-semibold text-yellow-600">{{ $stats['pending_bookings'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Halls -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Available Halls</h2>
            <a href="{{ route('halls.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                View all →
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($halls as $hall)
            <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                <div class="h-48 bg-gray-200 flex items-center justify-center">
                    @if($hall->image_url)
                    <img src="{{ $hall->image_url }}" alt="{{ $hall->name }}" class="h-full w-full object-cover">
                    @else
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $hall->name }}</h3>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $hall->description }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-900">FCFA {{ $hall->formatted_price }}/hr</span>
                        <span class="text-sm text-gray-500">{{ $hall->capacity }} guests</span>
                    </div>
                    <a href="{{ route('bookings.create.hall', $hall->id) }}" 
                       class="mt-4 w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Book Now
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Upcoming Events</h2>
            <a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                View all →
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($events as $event)
            <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                <div class="h-48 bg-gray-200 flex items-center justify-center">
                    @if($event->image_url)
                    <img src="{{ $event->image_url }}" alt="{{ $event->name }}" class="h-full w-full object-cover">
                    @else
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $event->name }}</h3>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $event->description }}</p>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-500">{{ $event->formatted_date }}</span>
                        <span class="text-sm font-medium text-gray-900">FCFA {{ $event->formatted_price }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mb-3">
                        {{ $event->available_slots - $event->booked_slots }} tickets remaining
                    </div>
                    <a href="{{ route('bookings.create.event', $event->id) }}" 
                       class="w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Get Tickets
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Bookings -->
    @if($recentBookings->count() > 0)
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Recent Bookings</h2>
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($recentBookings as $booking)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-indigo-600 truncate">{{ $booking->resource->name }}</p>
                                <p class="text-sm text-gray-500">{{ $booking->booking_date->format('M d, Y') }}</p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
@endsection
