<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\RetailOfficeListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RetailOfficeTurnOverConditions>
 */
class RetailOfficeTurnOverConditionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'turnover_remarks' => $this -> faker -> sentence(),
            'frontage_turnover'=> $this -> faker -> randomFloat(2,1,100),
            'retail_office_listing_id' => RetailOfficeListing::factory(),
        ];
    }
}
