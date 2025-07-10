<?php

namespace Database\Factories\ListingRelated\OtherDetailRelated;

use App\Enums\Meter;
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
            'electricity_meter' => $this->faker->randomElement(Meter::cases()), 
            'water_meter' => $this->faker->randomElement(Meter::cases()),       
            'year_built' => $this->faker->year(),
        ];
    }
}
