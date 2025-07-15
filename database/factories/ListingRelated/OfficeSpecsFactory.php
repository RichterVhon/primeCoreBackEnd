<?php

namespace Database\Factories\ListingRelated;

use App\Enums\AccreditationType;
use App\Models\ListingRelated\OfficeSpaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\OfficeSpecs>
 */
class OfficeSpecsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'density_ratio' => $this->faker->randomFloat(2,4.0,5.0),
            'floor_to_ceiling_height' => $this ->faker-> randomFloat(2,3.0,4.0),
            'floor_to_floor' => $this ->faker-> randomFloat(2,3.0,4.0),
            'accreditation' => $this -> faker-> randomElement(AccreditationType::cases()),
            'certification' => $this -> faker -> randomElement(['Leed Certified','Leed Silver','Leed Gold','Leed Platinum']),
            'office_space_listing_id' => OfficeSpaceListing::factory(),
        ];
    }
}
