<?php

namespace Database\Factories\ListingRelated;

use App\Models\Account;
use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'status' => $this->faker->randomElement(ListingStatus::cases()),
            'date_uploaded' => $uploaded = $this->faker->optional(0.8)->dateTimeBetween('-2 months', 'now'),
            'date_last_updated' => $this->faker->optional(0.8)->dateTimeBetween($uploaded, 'now'),
            'project_name' => $this->faker->optional(0.5)->company,
            'property_name' => $this->faker->optional(0.9)->streetName,
            'bd_incharge' => $this->faker->optional(0.8)->name,
            'authority_type' => $this->faker->optional(0.8)->randomElement(['exclusive', 'non-exclusive']), 
            'bd_securing_remarks' => $this->faker->optional(0.8)->paragraph,
            'listable_id' => null,              // polymorphic
            'listable_type' => null,
        ];
    }
}
