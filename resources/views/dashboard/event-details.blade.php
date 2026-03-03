@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                @if($event->image_url)
                <div class="w-full h-80 mb-8 rounded-lg overflow-hidden relative">
                    <img src="{{ Str::startsWith($event->image_url, ['http://', 'https://', '/']) ? $event->image_url : asset('storage/' . $event->image_url) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                </div>
                @endif
                
                @if($event->other_images && count($event->other_images) > 0)
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Gallery</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($event->other_images as $img)
                            <div class="h-32 rounded-lg overflow-hidden shadow-sm">
                                <img src="{{ asset('storage/' . $img) }}" alt="Gallery image for {{ $event->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="md:flex md:justify-between">
                    <div class="md:w-2/3">
                        <h2 class="text-3xl font-bold mb-4">{{ $event->name }}</h2>
                        <p class="text-gray-600 text-lg mb-6">{{ $event->description }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <span class="font-bold text-gray-700">Date:</span> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Time:</span> {{ $event->starts_at }} - {{ $event->ends_at }}
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Location:</span> {{ $event->location }}
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Availability:</span> {{ $event->available_slots }} slots left
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:w-1/3 mt-6 md:mt-0 md:pl-8 border-t md:border-t-0 md:border-l border-gray-200">
                        <div class="text-3xl font-bold text-green-600 mb-4">
                            FCFA {{ number_format($event->ticket_price, 0) }}
                            <span class="text-base text-gray-500 font-normal">/ ticket</span>
                        </div>

                        <a href="{{ route('bookings.create.event', $event->id) }}" 
                           class="block w-full text-center bg-green-600 text-white font-bold py-3 px-4 rounded hover:bg-green-700 transition">
                            Book Tickets
                        </a>
                        
                        <div class="mt-4 text-sm text-gray-500">
                            * Pricing is per person.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
