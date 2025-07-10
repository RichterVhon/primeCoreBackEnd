<?php

namespace Database\Factories\ListingRelated;

use App\Enums\Province;
use App\Models\ListingRelated\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(), 
            'province' => $this->faker->randomElement(Province::cases()),
            'city' => $this->faker->city(),
            'district' => $this->faker->streetName(),
            'google_coordinates_latitude' => $this->faker->latitude($min = 5.0, $max = 20.0), // Philippine bounds
            'google_coordinates_longitude' => $this->faker->longitude($min = 120.0, $max = 125.0),
            'exact_address' => $this->faker->address(),
        ];
    }
}
