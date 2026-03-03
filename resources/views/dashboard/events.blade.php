@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ __('Upcoming Events') }}
        </h2>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar Filters -->
            {{-- Filtering form for narrowing down upcoming events --}}
            <div class="w-full md:w-1/4">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 border-b pb-2">Filters</h3>
                    
                    {{-- Form auto-submits on attribute change via Alpine.js --}}
                    <form action="{{ route('events.index') }}" method="GET" x-data>
                        <!-- Location Filter -->
                        <div class="mb-5">
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Region/Location</label>
                            <select name="location" id="location" @change="$el.closest('form').submit()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->name }}" {{ request('location') == $loc->name ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="mb-5">
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <select name="date" id="date" @change="$el.closest('form').submit()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Any Time</option>
                                <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week" {{ request('date') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ request('date') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="mb-5">
                            <label for="price_range" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                            <select name="price_range" id="price_range" @change="$el.closest('form').submit()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Any Price</option>
                                <option value="free" {{ request('price_range') == 'free' ? 'selected' : '' }}>Free</option>
                                <option value="paid" {{ request('price_range') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Grid -->
            {{-- Displays the paginated list of events matching filter criteria --}}
            <div class="w-full md:w-3/4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @forelse($events as $event)
                                <div class="border rounded-lg bg-white overflow-hidden shadow-sm hover:shadow-md transition flex flex-col h-full">
                                    @if($event->image_url)
                                    <div class="h-48 bg-gray-200 w-full shrink-0">
                                        <img src="{{ Str::startsWith($event->image_url, ['http://', 'https://', '/']) ? $event->image_url : asset('storage/' . $event->image_url) }}" alt="{{ $event->name }}" class="h-full w-full object-cover">
                                    </div>
                                    @endif
                                    <div class="p-4 flex flex-col flex-grow">
                                        <h3 class="font-bold text-lg mb-2">{{ $event->name }}</h3>
                                        <p class="text-sm text-gray-500 mb-2 font-medium">
                                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $event->location }}
                                        </p>
                                        <p class="text-sm text-gray-500 mb-3 font-medium">
                                            <i class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }} 
                                            at {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                        </p>
                                        <p class="text-gray-600 mb-4 flex-grow">{{ Str::limit($event->description, 80) }}</p>
                                        
                                        <div class="flex flex-col mt-auto pt-4 border-t border-gray-100">
                                            <span class="text-green-600 font-bold mb-3">
                                                @if($event->ticket_price == 0)
                                                    Free
                                                @else
                                                    FCFA {{ number_format($event->ticket_price, 0) }}
                                                @endif
                                            </span>
                                            <a href="{{ route('events.show', $event->id) }}" class="w-full text-center bg-green-50 text-green-700 font-medium px-4 py-2 rounded hover:bg-green-100 transition">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-lg">No events match your current filters.</p>
                                    <p class="text-sm mt-2"><a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-800">Clear filters</a> to see all options.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-8">
                            {{ $events->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
