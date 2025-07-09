<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_person' => $this->faker->name(),
            'position' => $this->faker->optional(0.8)->jobTitle(),
            'contact_number' => $this->faker->optional(0.8)->phoneNumber(),
            'email_address' => $this->faker->optional(0.8)->safeEmail(),
        ];
    }
}
