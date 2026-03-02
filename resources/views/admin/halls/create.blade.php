@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Hall') }}
            </h2>
            <a href="{{ route('admin.halls') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Halls
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route('admin.halls.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Hall Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div class="col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                        </div>
                        
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}" required 
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (People)</label>
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" required min="1"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="price_per_hour" class="block text-sm font-medium text-gray-700">Price per Hour ($)</label>
                            <input type="number" name="price_per_hour" id="price_per_hour" value="{{ old('price_per_hour') }}" required min="0" step="0.01"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="image_url" class="block text-sm font-medium text-gray-700">Image URL (Optional)</label>
                            <input type="text" name="image_url" id="image_url" value="{{ old('image_url') }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
                            Create Hall
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
