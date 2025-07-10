<?php

namespace Database\Factories\ListingRelated;

use App\Enums\LotCondition;
use App\Models\ListingRelated\CommLotListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\CommLotTurnOverConditions>
 */
class CommLotTurnOverConditionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
return [
    'comm_lot_listing_id' => CommLotListing::factory(), // foreign key factory
    'lot_condition' => $this->faker->randomElement(LotCondition::cases())->value,
    'turnover_remarks' => $this->faker->optional()->sentence(10),
];
    }
}
