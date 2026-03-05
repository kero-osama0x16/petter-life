<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['vaccination', 'checkup', 'medication']),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'date' => fake()->date('Y-m-d'),
        ];
    }
}
