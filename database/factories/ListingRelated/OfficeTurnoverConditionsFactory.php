<?php

namespace Database\Factories\ListingRelated;

use App\Enums\HandoverType;
use App\Models\ListingRelated\OfficeSpaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\OfficeTurnoverConditions>
 */
class OfficeTurnoverConditionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'handover' => $this->faker->randomElement(HandoverType::cases()), // Enum-a eilish
            'ceiling' => $this->faker->randomElement(['Acoustic','T-Bone']), //Pls replace with data required
            'floor' => $this->faker->randomElement(['Smooth Cement','Rough']), // (2)
            'wall' => $this->faker->randomElement(['Painted', 'Raw', 'Partitioned']), // (3)
            'turnover_remarks' => $this->faker->sentence(),
            'office_space_listing_id' => OfficeSpaceListing::factory(),
        ];
    }
}
