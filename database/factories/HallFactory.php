<?php

namespace Database\Factories;

use App\Models\Hall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hall>
 */
class HallFactory extends Factory
{
    protected $model = Hall::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' Hall',
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->address(),
            'capacity' => $this->faker->numberBetween(50, 500),
            'price_per_hour' => $this->faker->numberBetween(20, 100),
            'amenities' => $this->faker->randomElements(['Parking', 'Air Conditioning', 'Sound System', 'Stage', 'Kitchen', 'Projector', 'Wi-Fi', 'Security'], $this->faker->numberBetween(2, 5)),
            'image_url' => '/images/hall-' . $this->faker->numberBetween(1, 5) . '.jpg',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the hall is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
