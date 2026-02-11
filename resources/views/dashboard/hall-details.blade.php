@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="md:flex md:justify-between">
                    <div class="md:w-2/3">
                        <h2 class="text-3xl font-bold mb-4">{{ $hall->name }}</h2>
                        <p class="text-gray-600 text-lg mb-6">{{ $hall->description }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <span class="font-bold text-gray-700">Location:</span> {{ $hall->location }}
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Capacity:</span> {{ $hall->capacity }} People
                            </div>
                        </div>

                        @if($hall->amenities && is_array($hall->amenities))
                            <div class="mb-6">
                                <h3 class="font-bold text-lg mb-2">Amenities</h3>
                                <ul class="list-disc list-inside">
                                    @foreach($hall->amenities as $amenity)
                                        <li>{{ $amenity }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <div class="md:w-1/3 mt-6 md:mt-0 md:pl-8 border-t md:border-t-0 md:border-l border-gray-200">
                        <div class="text-3xl font-bold text-blue-600 mb-4">
                            FCFA {{ number_format($hall->price_per_hour, 0) }}
                            <span class="text-base text-gray-500 font-normal">/ hour</span>
                        </div>

                        <a href="{{ route('bookings.create.hall', $hall->id) }}" 
                           class="block w-full text-center bg-blue-600 text-white font-bold py-3 px-4 rounded hover:bg-blue-700 transition">
                            Book This Hall
                        </a>
                        
                        <div class="mt-4 text-sm text-gray-500">
                            * Pricing may vary based on duration and extras.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
