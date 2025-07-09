<?php

namespace Database\Factories\ListingRelated;

use App\Enums\LotShape;
use App\Enums\Offering;
use App\Enums\ZoningClassification;
use App\Models\ListingRelated\IndLotListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\IndLotListingPropertyDetails>
 */
class IndLotListingPropertyDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ind_lot_listing_id' => IndLotListing::factory(), // assumes related factory exists
            'lot_area' => $this->faker->randomFloat(2, 50, 1000),
            'lot_shape' => $this->faker->randomElement(LotShape::cases()),
            'frontage_width' => $this->faker->randomFloat(2, 5, 50),
            'depth' => $this->faker->randomFloat(2, 10, 100),
            'zoning_classification' => $this->faker->randomElement(ZoningClassification::cases()),
            'offering' => $this->faker->randomElement(Offering::cases()),
        ];
    }
}
