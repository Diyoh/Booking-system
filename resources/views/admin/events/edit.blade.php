@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Event') }}: {{ $event->name }}
            </h2>
            <a href="{{ route('admin.events') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Events
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('admin.events.update', $event->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Event Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $event->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="event_date" id="event_date" value="{{ old('event_date', \Carbon\Carbon::parse($event->event_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('event_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" name="start_time" id="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($event->start_time)->format('H:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('start_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" name="end_time" id="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($event->end_time)->format('H:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('end_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="available_slots" class="block text-sm font-medium text-gray-700">Total Tickets</label>
                            <input type="number" name="available_slots" id="available_slots" value="{{ old('available_slots', $event->available_slots) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('available_slots') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="ticket_price" class="block text-sm font-medium text-gray-700">Ticket Price ($)</label>
                            <input type="number" step="0.01" name="ticket_price" id="ticket_price" value="{{ old('ticket_price', $event->ticket_price) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('ticket_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $event->description) }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="image_url" class="block text-sm font-medium text-gray-700">Image URL (Optional)</label>
                            <input type="url" name="image_url" id="image_url" value="{{ old('image_url', $event->image_url) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('image_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
