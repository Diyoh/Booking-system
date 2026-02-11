@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ __('Upcoming Events') }}
        </h2>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($events as $event)
                        <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition">
                            <h3 class="font-bold text-lg mb-2">{{ $event->name }}</h3>
                            <p class="text-gray-600 mb-2">{{ Str::limit($event->description, 100) }}</p>
                            <p class="text-sm text-gray-500 mb-2">
                                <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                            </p>
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-green-600 font-bold">FCFA {{ number_format($event->ticket_price, 0) }}</span>
                                <a href="{{ route('events.show', $event->id) }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 col-span-3 text-center">No upcoming events found.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
