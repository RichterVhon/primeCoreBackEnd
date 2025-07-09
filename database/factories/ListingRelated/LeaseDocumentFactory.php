<?php

namespace Database\Factories\ListingRelated;

use App\Models\ListingRelated\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\LeaseDocument>
 */
class LeaseDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $feeStructures = [
            '3% of annual rent',
            'Fixed ₱25,000 fee',
            'One-month equivalent rent',
            '5% upon signing + 2% annual',
        ];

        return [
            'listing_id' => Listing::factory(),
            'photos_and_property_documents_link' => $this->faker->optional(0.7)->url(),
            'professional_fee_structure' => $this->faker->optional(0.8)->randomElement($feeStructures),
        ];
    }
}
