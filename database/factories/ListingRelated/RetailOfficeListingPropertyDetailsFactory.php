<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\RetailOfficeListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RetailOfficeListingPropertyDetails>
 */
class RetailOfficeListingPropertyDetailsFactory extends Factory
{
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $floornum =[
            'GF',
            '2F',
            'Basement',
            '3F',
            'Lower Penthouse',
            'Upper Penthouse',
            'LGF',
            'UGF',
            'MZ',
            'EGF',
            'SF',
            'Basement',
            'GF+2F',
            '4th floor',
            '2nd floor',
            '3rd floor',
            'UG',
            'LG',
            'TF',
        ];

        $unitnum = [
            'Leaseable A',
            'Leaseable B',
            'Leaseable C',
            'Leaseable D',
            'Leaseable E',
            'Leaseable F',
            'Leaseable G',
            'Store A',
            'Store B',
            'Store C',
            'Store D',
            'Store E',
            'Store F',
            'Leaseable E',
            'Leaseable F',
            'Leaseable G',
            'Leaseable H',
            'Leaseable I',
        ];

        return [
            'floor_level'=> $this -> faker -> randomElement($floornum),
            'unit_number'=> $this -> faker -> randomElement($unitnum),
            'leasable_size'=> $this -> faker -> randomFLoat(2,10.0,1000.0),
            'retail_office_listing_id' => RetailOfficeListing::factory(),
        ];
    }
}
