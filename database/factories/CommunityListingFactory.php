<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommunityListing>
 */
class CommunityListingFactory extends Factory
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
            'user_id' => User::factory(),
            'listing_type' => fake()->randomElement(['adoption', 'breeding']),
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Mark listing as adoption.
     */
    public function adoption(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'adoption',
        ]);
    }

    /**
     * Mark listing as breeding.
     */
    public function breeding(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'breeding',
        ]);
    }

    /**
     * Mark listing as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
