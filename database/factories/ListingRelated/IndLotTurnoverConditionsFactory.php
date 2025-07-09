<?php

namespace Database\Factories\ListingRelated;

use App\Enums\LotCondition;
use App\Models\ListingRelated\IndLotListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\IndLotTurnoverConditions>
 */
class IndLotTurnoverConditionsFactory extends Factory
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
            'lot_condition' => $this->faker->randomElement(LotCondition::cases()),
            'turnover_remarks' => $this->faker->optional()->paragraph,
        ];
    }
}
