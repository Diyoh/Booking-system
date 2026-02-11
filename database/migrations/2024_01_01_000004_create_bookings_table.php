<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bookings Table Migration
 * 
 * Central table managing all resource reservations from both Web and USSD.
 * Implements polymorphic relationship (resource_type + resource_id) to support
 * both halls and events in a single table.
 * 
 * Critical Features:
 * - Reservation hold system (5-minute grace period for payment)
 * - Status tracking (pending -> confirmed/cancelled/expired)
 * - Unique reference codes for offline verification
 * - Source tracking (web vs ussd) for analytics
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // User who made the booking
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Polymorphic resource (Hall or Event)
            $table->enum('resource_type', ['hall', 'event'])->comment('Type of resource being booked');
            $table->unsignedBigInteger('resource_id')->comment('ID of the hall or event');
            
            // Booking details for HALLS (time-based)
            $table->date('booking_date')->comment('Date of the booking');
            $table->time('start_time')->nullable()->comment('Start time for hall bookings');
            $table->time('end_time')->nullable()->comment('End time for hall bookings');
            
            // Booking details for EVENTS (quantity-based)
            $table->integer('quantity')->default(1)->comment('Number of tickets for event bookings');
            
            // Financial
            $table->decimal('total_amount', 10, 2)->comment('Total cost of the booking');
            
            // Booking lifecycle management
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])
                  ->default('pending')
                  ->comment('Booking status: pending awaits payment, confirmed after payment success');
            
            // Reference code for offline verification (e.g., at venue entrance)
            $table->string('reference_code', 20)->unique()->comment('Unique code like "EVT-8821" for ticket verification');
            
            // Reservation hold expiry (prevents indefinite pending bookings)
            $table->timestamp('hold_expires_at')->nullable()->comment('5-minute hold for payment, null after confirmation');
            $table->timestamp('confirmed_at')->nullable()->comment('Timestamp when payment succeeded');
            
            // Analytics: track booking source
            $table->enum('source', ['web', 'ussd'])->comment('Interface used to make the booking');
            
            $table->timestamps();
            
            // Composite index for availability checking (critical for race condition prevention)
            $table->index(['resource_type', 'resource_id', 'booking_date', 'status']);
            
            // Index for querying user bookings
            $table->index(['user_id', 'status']);
            
            // Index for finding expired hold bookings (cron job)
            $table->index(['status', 'hold_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
