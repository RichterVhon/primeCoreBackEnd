<?php

namespace Database\Factories\ListingRelated;

use App\Enums\ElectricalLoadCapacity;
use App\Enums\LoadingBayVehicularCapacity;
use App\Enums\TypeOfLoadingBay;
use App\Enums\VehicleCapacity;
use App\Models\ListingRelated\WarehouseListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\WarehouseSpecs>
 */
class WarehouseSpecsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
   return [
    'warehouse_listing_id' => WarehouseListing::factory(), // assumes a related factory
    'application_of_cusa' => $this->faker->words(3, true),
    'apex' => $this->faker->randomFloat(2, 3, 15),
    'shoulder_height' => $this->faker->randomFloat(2, 2, 12),
    'dimensions_of_the_entrance' => $this->faker->randomFloat(2, 1.5, 8),
    'parking_allotment' => $this->faker->numberBetween(0, 40),
    'loading_bay' => $this->faker->numberBetween(0, 10),
    'loading_bay_elevation' => $this->faker->randomFloat(2, 0.5, 2.5),
    'type_of_loading_bay' => $this->faker->randomElement(TypeOfLoadingBay::cases()),
    'loading_bay_vehicular_capacity' => $this->faker->randomElement(VehicleCapacity::cases()),
    'electrical_load_capacity' => $this->faker->randomElement(ElectricalLoadCapacity::cases()),
    'vehicle_capacity' => $this->faker->randomElement(VehicleCapacity::cases()),
    'concrete_floor_strength' => $this->faker->randomFloat(2, 3000, 6000), // PSI strength
    'parking_rate_slot' => $this->faker->randomFloat(2, 1000, 5000),
];
    }
}
