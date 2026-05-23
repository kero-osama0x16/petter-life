<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdoptionRequest>
 */
class AdoptionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_id' => Pet::factory(),
            'requester_id' => User::factory(),
            'pet_owner_id' => User::factory(),
            'request_type' => fake()->randomElement(['adoption', 'breeding']),
            'status' => 'pending',
            'message' => fake()->sentence(),
            'responded_at' => null,
        ];
    }

    /**
     * Mark request as adoption.
     */
    public function adoption(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'adoption',
        ]);
    }

    /**
     * Mark request as breeding.
     */
    public function breeding(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => 'breeding',
        ]);
    }

    /**
     * Mark request as accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
    }

    /**
     * Mark request as rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }

    /**
     * Mark request as pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'responded_at' => null,
        ]);
    }
}
