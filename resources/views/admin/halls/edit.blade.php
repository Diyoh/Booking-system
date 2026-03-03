@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Hall') }}: {{ $hall->name }}
            </h2>
            <a href="{{ route('admin.halls') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Halls
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('admin.halls.update', $hall->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $hall->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <select name="location" id="location" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select a Location</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->name }}" {{ old('location', $hall->location) == $loc->name ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $hall->capacity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="price_per_hour" class="block text-sm font-medium text-gray-700">Price per Hour (FCFA)</label>
                            <input type="number" step="0.01" name="price_per_hour" id="price_per_hour" value="{{ old('price_per_hour', $hall->price_per_hour) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('price_per_hour') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $hall->description) }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded border border-gray-200 mt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Images</h3>
                            
                            <!-- Current Front Image -->
                            @if($hall->image_url)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current Main Image:</p>
                                    <img src="{{ Str::startsWith($hall->image_url, ['http://', 'https://']) ? $hall->image_url : asset('storage/' . $hall->image_url) }}" alt="Front Image" class="h-32 w-auto object-cover rounded shadow">
                                </div>
                            @endif

                            <div class="mb-6">
                                <label for="front_image" class="block text-sm font-medium text-gray-700">Update Main Image (Optional)</label>
                                <input type="file" name="front_image" id="front_image" accept="image/*" class="mt-1 flex w-full border-gray-300 rounded-md shadow-sm text-sm p-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image. Recommended size: 800x600px, max 2MB.</p>
                                @error('front_image')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Current Other Images -->
                            @if($hall->other_images && count($hall->other_images) > 0)
                                <div class="mb-4 border-t border-gray-200 pt-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Current Gallery Images:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($hall->other_images as $img)
                                            <img src="{{ asset('storage/' . $img) }}" alt="Gallery Image" class="h-20 w-20 object-cover rounded shadow">
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-2">
                                <label for="other_images" class="block text-sm font-medium text-gray-700">Upload New Gallery Images (Optional)</label>
                                <input type="file" name="other_images[]" id="other_images" multiple accept="image/*" class="mt-1 flex w-full border-gray-300 rounded-md shadow-sm text-sm p-2 bg-white">
                                <p class="text-xs text-gray-500 mt-1">Warning: Uploading new images here will completely replace your current gallery images. Max 2MB per image.</p>
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
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Hall
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
