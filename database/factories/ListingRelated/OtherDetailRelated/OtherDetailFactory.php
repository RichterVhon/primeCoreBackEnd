<?php

namespace Database\Factories\ListingRelated\OtherDetailRelated;

use App\Models\ListingRelated\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\OtherDetailRelated\OtherDetail>
 */
class OtherDetailFactory extends Factory
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
            'electricity_meter' => $this->faker->boolean(80), // 80% chance true
            'water_meter' => $this->faker->boolean(75),       // 75% chance true
            'year_built' => $this->faker->year(),
        ];
    }
}
