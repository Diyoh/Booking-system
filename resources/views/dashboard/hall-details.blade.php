@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                @if($hall->image_url)
                <div class="w-full h-80 mb-8 rounded-lg overflow-hidden relative">
                    <img src="{{ Str::startsWith($hall->image_url, ['http://', 'https://', '/']) ? $hall->image_url : asset('storage/' . $hall->image_url) }}" alt="{{ $hall->name }}" class="w-full h-full object-cover">
                </div>
                @endif
                
                @if($hall->other_images && count($hall->other_images) > 0)
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Gallery</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($hall->other_images as $img)
                            <div class="h-32 rounded-lg overflow-hidden shadow-sm">
                                <img src="{{ asset('storage/' . $img) }}" alt="Gallery image for {{ $hall->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
