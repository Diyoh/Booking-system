<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * USSD Sessions Table Migration
 * 
 * Critical for USSD state management. USSD is stateless by protocol,
 * but we need to "remember" the user's position in the menu hierarchy.
 * 
 * Each USSD session gets a unique session_id from the MNO (Mobile Network Operator).
 * We store the current menu state and any temporary data (e.g., selected hall, date input).
 * 
 * Sessions expire after MNO timeout (typically 180 seconds) or earlier if user completes action.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ussd_sessions', function (Blueprint $table) {
            $table->id();
            
            // MNO-provided session identifier (unique per USSD session)
            $table->string('session_id')->unique()->comment('Session ID from Africa\'s Talking/MNO');
            
            // User identification
            $table->string('phone_number', 20)->comment('User\'s phone number (MSISDN)');
            
            // State machine tracking
            $table->string('current_menu')->comment('Current menu state: main, browse_halls, select_date, etc.');
            
            // Temporary session data (JSON for flexibility)
            // Examples: {"selected_hall_id": 3, "page": 2, "date_input": "15-02-2026"}
            $table->json('menu_data')->nullable()->comment('Temporary data for current session');
            
            // Last user input (for error handling and back navigation)
            $table->string('last_input')->nullable();
            
            // Auto-expire mechanism
            $table->timestamp('expires_at')->comment('Session expiry time (180s from creation)');
            
            $table->timestamps();
            
            // Index for quick session lookup
            $table->index('session_id');
            
            // Index for finding expired sessions (cleanup cron job)
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_sessions');
    }
};
