<?php

namespace Database\Factories\ListingRelated;

use App\Enums\YesNo;
use App\Models\ListingRelated\WarehouseListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\WarehouseListingPropDetails>
 */
class WarehouseListingPropDetailsFactory extends Factory
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
            'unit_number' => $this->faker->bothify('Unit ###-??'),
            'leasable_warehouse_area_on_the_ground_floor' => $this->faker->randomFloat(2, 50, 1000),
            'leasable_warehouse_area_on_the_upper_floor' => $this->faker->randomFloat(2, 50, 800),
            'leasable_office_area' => $this->faker->randomFloat(2, 30, 500),
            'total_leasable_area' => $this->faker->randomFloat(2, 100, 2000),
            'total_open_area' => $this->faker->randomFloat(2, 50, 1000),
            'total_leasable_area_open_covered' => $this->faker->randomFloat(2, 20, 500),
            'FDAS' => $this->faker->randomElement(YesNo::cases()), 
        ];
    }
}   
