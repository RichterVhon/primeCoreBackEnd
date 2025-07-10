<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\WarehouseListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\WarehouseTurnoverConditions>
 */
class WarehouseTurnoverConditionsFactory extends Factory
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
            'flooring_turnover' => $this->faker->words(2, true), 
            'ceiling_turnover' => $this->faker->words(2, true), 
            'wall_turnover' => $this->faker->words(2, true),
            'turnover_remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
