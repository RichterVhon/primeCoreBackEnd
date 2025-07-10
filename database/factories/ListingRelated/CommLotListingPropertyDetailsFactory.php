<?php

namespace Database\Factories\ListingRelated;

use App\Enums\LotShape;
use App\Enums\ZoningClassification;
use App\Models\ListingRelated\CommLotListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\CommLotListingPropertyDetails>
 */
class CommLotListingPropertyDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
return [
    'comm_lot_listing_id' => CommLotListing::factory(),
    'lot_area' => $this->faker->randomFloat(2, 50, 5000), // in sqm
    'lot_shape' => $this->faker->randomElement(LotShape::cases()),
    'frontage_width' => $this->faker->randomFloat(2, 5, 50),
    'depth' => $this->faker->randomFloat(2, 10, 100),
    'zoning_classification' => $this->faker->randomElement(ZoningClassification::cases()),
];
    }
}
