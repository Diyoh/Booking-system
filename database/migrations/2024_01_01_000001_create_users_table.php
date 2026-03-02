<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Users Table Migration
 * 
 * This table stores all user accounts for both Web and USSD interfaces.
 * Phone number serves as the unique identifier linking both authentication methods.
 * 
 * - Web users: email + password required
 * - USSD users: phone + ussd_pin required (email optional)
 * - Hybrid users: Can use both interfaces with same phone number
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Phone number is the common identifier for both Web and USSD
            $table->string('phone_number', 20)->unique()->comment('Primary identifier, format: +254XXXXXXXXX');
            
            // Web authentication fields
            $table->string('email')->nullable()->unique()->comment('Optional for USSD-only users');
            $table->string('password')->nullable()->comment('Hashed password for web login, null for USSD-only');
            
            // User profile information
            $table->string('name')->comment('Full name of the user');
            
            // USSD authentication field
            $table->string('ussd_pin', 4)->nullable()->comment('4-digit PIN for USSD transactions, encrypted');
            
            // User permissions
            $table->boolean('is_admin')->default(false)->comment('Admin flag for dashboard access');
            
            // Laravel default fields
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('email');
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
