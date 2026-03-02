<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transactions Table Migration
 * 
 * Tracks all payment transactions initiated via Africa's Talking.
 * Links to bookings table for payment-booking reconciliation.
 * Stores provider responses for audit trail and debugging.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Linked booking
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            
            // Payment details
            $table->string('phone_number', 20)->comment('Phone number used for payment');
            $table->decimal('amount', 10, 2)->comment('Amount charged');
            
            // Africa's Talking transaction tracking
            $table->string('provider_transaction_id')->nullable()->comment('Transaction ID from Africa\'s Talking');
            
            // Transaction status
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            
            // Store full provider response for debugging and audit
            $table->json('provider_response')->nullable()->comment('Full callback payload from Africa\'s Talking');
            
            $table->timestamps();
            
            // Index for querying by booking
            $table->index('booking_id');
            
            // Index for finding pending transactions (for manual reconciliation if needed)
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
