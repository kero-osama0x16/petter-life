<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'type' => fake()->randomElement(['dog', 'cat', 'bird', 'rabbit']),
            'breed' => fake()->word(),
            'gender' => fake()->randomElement(['male', 'female']),
            'birthday' => fake()->date('Y-m-d'),
            'personality' => fake()->sentence(),
            'color' => fake()->colorName(),
        ];
    }
}
