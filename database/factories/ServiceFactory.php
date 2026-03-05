<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(['vet', 'groomer', 'trainer', 'shelter']),
            'lat' => fake()->latitude(),
            'long' => fake()->longitude(),
            'address' => fake()->address(),
            'rating' => fake()->randomFloat(1, 1, 5),
        ];
    }
}