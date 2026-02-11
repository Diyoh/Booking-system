@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Book Hall: {{ $hall->name }}</h2>

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Whoops!</strong>
                        <span class="block sm:inline">There were some problems with your input.</span>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <div class="mb-6">
                            <h3 class="font-bold text-lg mb-2">Hall Details</h3>
                            <p class="text-gray-600 mb-2">{{ $hall->description }}</p>
                            <p class="mb-1"><span class="font-semibold">Capacity:</span> {{ $hall->capacity }} People</p>
                            <p class="mb-1"><span class="font-semibold">Price per Hour:</span> FCFA {{ number_format($hall->price_per_hour, 0) }}</p>
                            <p class="mb-1"><span class="font-semibold">Location:</span> {{ $hall->location }}</p>
                            
                            @if($hall->amenities)
                                <div class="mt-4">
                                    <span class="font-semibold">Amenities:</span>
                                    <ul class="list-disc list-inside mt-1 ml-2">
                                        @foreach($hall->amenities as $amenity)
                                            <li>{{ $amenity }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <form action="{{ route('bookings.store.hall') }}" method="POST">
                            @csrf
                            <input type="hidden" name="hall_id" value="{{ $hall->id }}">

                            <div class="mb-4">
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date') }}" min="{{ date('Y-m-d') }}" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Rate per Hour:</span>
                                    <span class="font-semibold">FCFA {{ number_format($hall->price_per_hour, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-lg font-bold border-t pt-2 mt-2">
                                    <span>Estimated Total:</span>
                                    <span id="total-price">FCFA 0</span>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded hover:bg-blue-700 transition duration-150 ease-in-out">
                                Confirm & Pay
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calculateTotal() {
        const price = {{ $hall->price_per_hour }};
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        if (startTime && endTime) {
            const start = new Date("01/01/2000 " + startTime);
            const end = new Date("01/01/2000 " + endTime);
            
            let diff = (end - start) / 1000 / 60 / 60; // difference in hours
            
            if (diff < 0) diff += 24; // Handle overnight (simple case)
            if (diff <= 0) diff = 0;

            const total = price * diff;
            document.getElementById('total-price').innerText = 'FCFA ' + total.toFixed(0);
        }
    }

    document.getElementById('start_time').addEventListener('change', calculateTotal);
    document.getElementById('end_time').addEventListener('change', calculateTotal);
</script>
@endsection
