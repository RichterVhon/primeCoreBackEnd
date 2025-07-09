<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\IndLotListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\IndLotLeaseRates>
 */
class IndLotLeaseRatesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ind_lot_listing_id' => IndLotListing::factory(), 
            'rental_rate_sqm_for_open_area' => $this->faker->randomFloat(2, 50, 500),
            'rental_rate_sqm_for_covered_area' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
