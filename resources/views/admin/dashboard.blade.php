@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ __('Admin Dashboard') }}
        </h2>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm uppercase font-bold">Total Users</div>
                <div class="text-3xl font-bold">{{ $stats['total_users'] }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm uppercase font-bold">Active Bookings</div>
                <div class="text-3xl font-bold">{{ $stats['confirmed_bookings'] }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm uppercase font-bold">Pending Bookings</div>
                <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_bookings'] }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-gray-500 text-sm uppercase font-bold">Total Revenue</div>
                <div class="text-3xl font-bold text-green-600">FCFA {{ number_format($stats['total_revenue'], 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Bookings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="font-bold text-lg mb-4">Recent Bookings</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Resource</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentBookings as $booking)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $booking->user->name }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $booking->resource->name }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                   ($booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-sm text-gray-500 text-center">No recent bookings</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right">
                        <a href="{{ route('admin.bookings') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">View All &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Management Links -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="font-bold text-lg mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.halls.create') }}" class="block w-full text-center bg-blue-50 text-blue-700 font-semibold py-3 px-4 rounded hover:bg-blue-100 transition border border-blue-200">
                            + Add New Hall
                        </a>
                        <a href="{{ route('admin.events.create') }}" class="block w-full text-center bg-green-50 text-green-700 font-semibold py-3 px-4 rounded hover:bg-green-100 transition border border-green-200">
                            + Create New Event
                        </a>
                        <a href="{{ route('admin.users') }}" class="block w-full text-center bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded hover:bg-gray-100 transition border border-gray-200">
                            Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
