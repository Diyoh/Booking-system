<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events Table Migration
 * 
 * Stores event resources available for booking.
 * Events are booked by ticket quantity (seat-based).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->comment('Event name, e.g., "Annual Town Meeting"');
            $table->text('description')->comment('Detailed event description');
            
            // Event scheduling
            $table->date('event_date')->comment('Date when the event occurs');
            $table->time('start_time')->comment('Event start time');
            $table->time('end_time')->comment('Event end time');
            
            $table->string('location')->comment('Event venue/location');
            
            // Ticketing
            $table->decimal('ticket_price', 10, 2)->comment('Price per ticket');
            $table->integer('available_slots')->comment('Total tickets available');
            $table->integer('booked_slots')->default(0)->comment('Number of tickets already booked');
            
            $table->string('image_url')->nullable()->comment('URL/path to event poster/image');
            
            $table->boolean('is_active')->default(true)->comment('Active events shown to users');
            
            $table->timestamps();
            
            // Composite index for querying upcoming active events
            $table->index(['is_active', 'event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
