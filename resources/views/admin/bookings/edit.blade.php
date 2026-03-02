@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Booking') }}: #{{ $booking->id }}
            </h2>
            <a href="{{ route('admin.bookings') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Bookings
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Booking Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><strong class="text-gray-700">User:</strong> {{ $booking->user->name }} ({{ $booking->user->email }})</div>
                        <div><strong class="text-gray-700">Resource:</strong> {{ $booking->resource ? $booking->resource->name : 'N/A' }}</div>
                        <div><strong class="text-gray-700">Type:</strong> {{ ucfirst($booking->type) }}</div>
                        <div><strong class="text-gray-700">Total Amount:</strong> ${{ number_format($booking->total_amount, 2) }}</div>
                        <div><strong class="text-gray-700">Start Time:</strong> {{ \Carbon\Carbon::parse($booking->start_time)->format('Y-m-d H:i') }}</div>
                        <div><strong class="text-gray-700">End Time:</strong> {{ \Carbon\Carbon::parse($booking->end_time)->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.bookings.update', $booking->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="pending" {{ old('status', $booking->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ old('status', $booking->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ old('status', $booking->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount ($)</label>
                            <input type="number" step="0.01" name="total_amount" id="total_amount" value="{{ old('total_amount', $booking->total_amount) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('total_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Admin Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $booking->notes) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Internal notes, not visible to the user.</p>
                            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
