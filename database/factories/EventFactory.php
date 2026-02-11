<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        
        return [
            'name' => $this->faker->words(4, true),
            'description' => $this->faker->paragraph(4),
            'event_date' => $eventDate->format('Y-m-d'),
            'start_time' => $this->faker->time('H:i:s'),
            'end_time' => $this->faker->time('H:i:s'),
            'location' => $this->faker->address(),
            'ticket_price' => $this->faker->numberBetween(10, 100),
            'available_slots' => $this->faker->numberBetween(50, 500),
            'booked_slots' => 0,
            'image_url' => '/images/event-' . $this->faker->numberBetween(1, 5) . '.jpg',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the event is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_date' => $this->faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the event is sold out.
     */
    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'booked_slots' => $attributes['available_slots'],
        ]);
    }
}
