<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\OfficeSpaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfficeListingPropertyDetails>
 */
class OfficeListingPropertyDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'floor_level' => $this -> faker -> randomElement(['8th','7th']),
            'unit_number'=> $this -> faker -> randomElement(['801','800']),
            'leasable_size'=> $this -> faker -> randomFloat(2,1.0,100.0),
            'office_space_listing_id' => OfficeSpaceListing::factory(),
        ];
    }
}
