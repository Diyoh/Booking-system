@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ __('Available Halls') }}
        </h2>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($halls as $hall)
                        <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition">
                            <h3 class="font-bold text-lg mb-2">{{ $hall->name }}</h3>
                            <p class="text-gray-600 mb-2">{{ Str::limit($hall->description, 100) }}</p>
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-blue-600 font-bold">FCFA {{ number_format($hall->price_per_hour, 0) }}/hr</span>
                                <a href="{{ route('halls.show', $hall->id) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 col-span-3 text-center">No halls available at the moment.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $halls->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
