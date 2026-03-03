@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Event') }}
            </h2>
            <a href="{{ route('admin.events') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Events
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Event Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div class="col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                        </div>
                        
                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
                            <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" required min="{{ date('Y-m-d') }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <select name="location" id="location" required class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="">Select a Location</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->name }}" {{ old('location') == $loc->name ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="available_slots" class="block text-sm font-medium text-gray-700">Available Tickets</label>
                            <input type="number" name="available_slots" id="available_slots" value="{{ old('available_slots') }}" required min="1"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="ticket_price" class="block text-sm font-medium text-gray-700">Price per Ticket (FCFA)</label>
                            <input type="number" name="ticket_price" id="ticket_price" value="{{ old('ticket_price') }}" required min="0" step="0.01"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div class="col-span-2 bg-gray-50 p-4 rounded border border-gray-200">
                            <h3 class="font-medium text-lg mb-4">Images</h3>
                            <div class="mb-4">
                                <label for="front_image" class="block text-sm font-medium text-gray-700">Front Image (Main Cover)</label>
                                <input type="file" name="front_image" id="front_image" accept="image/*" class="mt-1 flex w-full border-gray-300 rounded-md shadow-sm text-sm p-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 800x600px, max 2MB. Optional.</p>
                                @error('front_image')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <label for="other_images" class="block text-sm font-medium text-gray-700">Other Images (Gallery)</label>
                                <input type="file" name="other_images[]" id="other_images" multiple accept="image/*" class="mt-1 flex w-full border-gray-300 rounded-md shadow-sm text-sm p-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">You can select multiple images. Max 2MB per image. Optional.</p>
                                @error('other_images')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                @error('other_images.*')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
