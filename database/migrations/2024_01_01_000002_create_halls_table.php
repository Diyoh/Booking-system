<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Halls Table Migration
 * 
 * Stores community hall resources available for booking.
 * Halls are booked by time slots (hourly basis).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->comment('Hall name, e.g., "Town Hall"');
            $table->text('description')->comment('Detailed description of the hall');
            $table->string('location')->comment('Physical address of the hall');
            
            $table->integer('capacity')->comment('Maximum number of people the hall can accommodate');
            $table->decimal('price_per_hour', 10, 2)->comment('Rental price per hour in local currency');
            
            // JSON field for amenities (flexible, can be extended)
            $table->json('amenities')->nullable()->comment('Array of amenities: ["Parking", "AC", "Sound System"]');
            
            $table->string('image_url')->nullable()->comment('URL/path to hall image');
            
            $table->boolean('is_active')->default(true)->comment('Soft delete flag, inactive halls not shown');
            
            $table->timestamps();
            
            // Index for active halls queries
            $table->index(['is_active', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
    }
};
