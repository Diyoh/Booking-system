@extends('layouts.app')

@section('title', 'Booking Confirmation')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Status Header -->
        <div class="px-6 py-8 text-center {{ $booking->status === 'confirmed' ? 'bg-green-50' : 'bg-yellow-50' }}">
            @if($booking->status === 'confirmed')
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Booking Confirmed!</h2>
            <p class="mt-2 text-gray-600">Your payment was successful</p>
            @else
            <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Payment Pending</h2>
            <p class="mt-2 text-gray-600">Please complete payment on your phone</p>
            @endif
        </div>

        <!-- Booking Details -->
        <div class="px-6 py-6 space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Booking Details</h3>
                
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Reference Code</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">{{ $booking->reference_code }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Resource</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->resource->name }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->booking_date->format('F d, Y') }}</dd>
                    </div>
                    
                    @if($booking->start_time)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Time</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}</dd>
                    </div>
                    @endif
                    
                    @if($booking->quantity > 1)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tickets</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->quantity }}</dd>
                    </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                        <dd class="mt-1 text-sm font-bold text-gray-900">FCFA {{ number_format($booking->total_amount, 0) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Transaction Info -->
            @if($booking->transaction)
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($booking->transaction->status === 'success') bg-green-100 text-green-800
                                @elseif($booking->transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($booking->transaction->status) }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->transaction->phone_number }}</dd>
                    </div>
                </dl>
            </div>
            @endif

            <!-- Important Notes -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">Important Information</h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @if($booking->status === 'confirmed')
                                <li>Save your reference code: <strong>{{ $booking->reference_code }}</strong></li>
                                <li>An SMS confirmation has been sent to your phone</li>
                                <li>Present your reference code when you arrive</li>
                                @else
                                <li>Complete the M-Pesa prompt on your phone within 5 minutes</li>
                                <li>Your booking will expire if payment is not completed</li>
                                <li>Check your phone for the STK Push notification</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 flex justify-between">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Back to Dashboard
            </a>
            <a href="{{ route('dashboard.bookings') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                View My Bookings
            </a>
        </div>
    </div>
</div>
@endsection
