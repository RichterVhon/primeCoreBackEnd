<?php

namespace Database\Factories\ListingRelated;

use App\Enums\TaxOnCusa;
use App\Models\ListingRelated\OfficeSpaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfficeLeaseTermsAndConditionsExtn>
 */
class OfficeLeaseTermsAndConditionsExtnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tax_on_cusa' =>  $this -> faker -> randomElement(TaxOnCusa::cases()),
            'cusa_on_parking' => $this ->faker -> randomFloat(2,50.00,125.00),
            'parking_rate_slot' => $this ->faker -> randomFloat(2,5000.00,10000.00),
            'parking_allotment' => $this ->faker -> numberBetween(3,5), 
            'office_space_listing_id' => null,
        ];
    }
}
