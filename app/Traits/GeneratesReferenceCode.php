<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * GeneratesReferenceCode Trait
 * 
 * Provides functionality to generate unique reference codes for bookings.
 * Format: TYPE-XXXX (e.g., HALL-1234, EVT-5678)
 * 
 * Used by Booking model for offline verification at venue entrance.
 */
trait GeneratesReferenceCode
{
    /**
     * Generate a unique reference code for the booking.
     * 
     * The code format is: {PREFIX}-{RANDOM_NUMBER}
     * - Hall bookings: HALL-XXXX
     * - Event bookings: EVT-XXXX
     * 
     * The method ensures uniqueness by checking the database.
     *
     * @return string
     */
    public function generateReferenceCode(): string
    {
        do {
            // Determine prefix based on resource type
            $prefix = $this->resource_type === 'event' ? 'EVT' : 'HALL';
            
            // Generate random 4-digit number
            $number = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            
            // Combine prefix and number
            $referenceCode = "{$prefix}-{$number}";
            
            // Check if this code already exists
            $exists = static::where('reference_code', $referenceCode)->exists();
            
        } while ($exists);  // Regenerate if duplicate found
        
        return $referenceCode;
    }
}
