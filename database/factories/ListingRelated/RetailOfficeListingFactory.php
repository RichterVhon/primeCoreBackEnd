<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\RetailOfficeListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\RetailOfficeListing>
 */
class RetailOfficeListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'floor_level' => $this-> faker -> randomElement([
                'Leasable A',
                'Leasable B',
                'Leasable C',
                'Store A',
                'Store B',
                'Store C',
            ]),

            'unit_number'=> $this-> faker -> randomElement([
                'GF',
                '2F',
                '3F',
            ]),
            'leasable_size'=> $this -> faker -> randomFloat(2, 50.0, 500.0),
            'retail_office_listing_id'=> RetailOfficeListing::factory(),
        ];
    }
}
