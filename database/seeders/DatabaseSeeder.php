<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Hall;
use App\Models\Event;
use Carbon\Carbon;

/**
 * DatabaseSeeder
 * 
 * Seed database with sample data for testing and demonstration.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'phone_number' => '+254712345678',
            'email' => 'admin@booking.com',
            'name' => 'Administrator',
            'password' => bcrypt('password123'),
            'ussd_pin' => '1234',
            'is_admin' => true,
        ]);

        // Create test users
        User::create([
            'phone_number' => '+254798765432',
            'email' => 'user@booking.com',
            'name' => 'John Doe',
            'password' => bcrypt('password123'),
            'ussd_pin' => '5678',
            'is_admin' => false,
        ]);

        // USSD-only user
        User::create([
            'phone_number' => '+254734567890',
            'name' => 'Jane Smith',
            'ussd_pin' => '9999',
            'is_admin' => false,
        ]);

        // Create sample halls
        Hall::create([
            'name' => 'Town Hall',
            'description' => 'Large community hall suitable for weddings, conferences, and large gatherings.',
            'location' => '123 Main Street, Nairobi',
            'capacity' => 200,
            'price_per_hour' => 50.00,
            'amenities' => ['Parking', 'Air Conditioning', 'Sound System', 'Stage', 'Kitchen'],
            'image_url' => '/images/town-hall.jpg',
            'is_active' => true,
        ]);

        Hall::create([
            'name' => 'Community Center',
            'description' => 'Medium-sized hall perfect for meetings and small events.',
            'location' => '456 Oak Avenue, Nairobi',
            'capacity' => 100,
            'price_per_hour' => 30.00,
            'amenities' => ['Parking', 'Projector', 'Wi-Fi'],
            'image_url' => '/images/community-center.jpg',
            'is_active' => true,
        ]);

        Hall::create([
            'name' => 'Conference Room',
            'description' => 'Professional conference room with modern facilities.',
            'location' => '789 Business Park, Nairobi',
            'capacity' => 50,
            'price_per_hour' => 40.00,
            'amenities' => ['Wi-Fi', 'Projector', 'Whiteboard', 'Video Conferencing'],
            'image_url' => '/images/conference-room.jpg',
            'is_active' => true,
        ]);

        // Create sample events
        Event::create([
            'name' => 'Annual Town Meeting',
            'description' => 'Yearly gathering of all community members to discuss town matters.',
            'event_date' => Carbon::now()->addDays(30),
            'start_time' => '18:00:00',
            'end_time' => '21:00:00',
            'location' => 'Town Hall, Main Street',
            'ticket_price' => 10.00,
            'available_slots' => 150,
            'booked_slots' => 0,
            'image_url' => '/images/town-meeting.jpg',
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Tech Workshop: Web Development',
            'description' => 'Learn modern web development with React and Laravel.',
            'event_date' => Carbon::now()->addDays(15),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'location' => 'Community Center',
            'ticket_price' => 25.00,
            'available_slots' => 40,
            'booked_slots' => 0,
            'image_url' => '/images/tech-workshop.jpg',
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Community Festival',
            'description' => 'Annual cultural festival with food, music, and entertainment.',
            'event_date' => Carbon::now()->addDays(60),
            'start_time' => '10:00:00',
            'end_time' => '22:00:00',
            'location' => 'Central Park',
            'ticket_price' => 15.00,
            'available_slots' => 500,
            'booked_slots' => 0,
            'image_url' => '/images/festival.jpg',
            'is_active' => true,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@booking.com / password123');
        $this->command->info('User: user@booking.com / password123');
        $this->command->info('USSD Test: +254734567890 / PIN: 9999');
    }
}
