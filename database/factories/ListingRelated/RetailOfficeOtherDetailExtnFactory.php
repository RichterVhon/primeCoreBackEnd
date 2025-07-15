<?php

namespace Database\Factories\ListingRelated;

use App\Enums\Pylonavailability;
use App\Models\ListingRelated\RetailOfficeListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RetailOfficeOtherDetailExtn>
 */
class RetailOfficeOtherDetailExtnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pylon_availability' => $this -> faker -> randomElement(Pylonavailability::cases()), // can be enum later on in the project
            'total_floor_count' => $this -> faker -> numberBetween(5,10),
            'other_remarks' => $this -> faker -> sentence(),
            'retail_office_listing_id' => RetailOfficeListing::factory(),
        ];
    }
}
