@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Book Event: {{ $event->name }}</h2>

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
                            <h3 class="font-bold text-lg mb-2">Event Details</h3>
                            <p class="text-gray-600 mb-2">{{ $event->description }}</p>
                            <p class="mb-1"><span class="font-semibold">Date:</span> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</p>
                            <p class="mb-1"><span class="font-semibold">Time:</span> {{ $event->starts_at }} - {{ $event->ends_at }}</p>
                            <p class="mb-1"><span class="font-semibold">Location:</span> {{ $event->location }}</p>
                            <p class="mb-1"><span class="font-semibold">Price per Ticket:</span> FCFA {{ number_format($event->ticket_price, 0) }}</p>
                            <p class="mb-1"><span class="font-semibold">Available Tickets:</span> {{ $event->available_slots }}</p>
                        </div>
                    </div>

                    <div>
                        <form action="{{ route('bookings.store.event') }}" method="POST">
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $event->id }}">

                            <div class="mb-6">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Number of Tickets</label>
                                <select name="quantity" id="quantity" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @for ($i = 1; $i <= min(10, $event->available_slots); $i++)
                                        <option value="{{ $i }}" {{ old('quantity', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <p class="mt-2 text-sm text-gray-500">Max 10 tickets per booking.</p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600">Ticket Price:</span>
                                    <span class="font-semibold">FCFA {{ number_format($event->ticket_price, 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-lg font-bold border-t pt-2 mt-2">
                                    <span>Total:</span>
                                    <span id="total-price">FCFA {{ number_format($event->ticket_price, 0) }}</span>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded hover:bg-green-700 transition duration-150 ease-in-out">
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
    document.getElementById('quantity').addEventListener('change', function() {
        const price = {{ $event->ticket_price }};
        const quantity = this.value;
        const total = price * quantity;
        document.getElementById('total-price').innerText = 'FCFA ' + total.toFixed(0);
    });
</script>
@endsection
