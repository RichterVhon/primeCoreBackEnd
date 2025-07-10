<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\WarehouseListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\WarehouseLeaseRates>
 */
class WarehouseLeaseRatesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
return [
    'warehouse_listing_id' => WarehouseListing::factory(),
    'rental_rate_sqm_for_open_area' => $this->faker->randomFloat(2, 50, 300), // PHP per sqm
    'rental_rate_sqm_for_covered_warehouse_area' => $this->faker->randomFloat(2, 100, 500),
    'rental_rate_sqm_for_office_area' => $this->faker->randomFloat(2, 150, 600),
];
    }
}
