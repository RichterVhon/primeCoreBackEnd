<?php

namespace Database\Factories\ListingRelated;

use App\Enums\ApplicationOfAdvance;
use App\Enums\EscalationFrequency;
use App\Models\ListingRelated\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\LeaseTermsAndConditions>
 */
class LeaseTermsAndConditionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //$frequencies = ['monthly', 'quarterly', 'annually']; moved to enum

        $minLease = $this->faker->optional()->numberBetween(6, 12);
        $maxLease = $minLease ? $this->faker->numberBetween($minLease + 6, $minLease + 48) : null;

        $shouldEscalate = $this->faker->boolean(60); // Only 60% of records get escalation logic

        return [
            'listing_id' => Listing::factory(),
            'monthly_rate' => $this->faker->optional()->randomFloat(2, 5000, 150000),
            'cusa_sqm' => $this->faker->optional()->randomFloat(2, 50, 200),
            'security_deposit' => $this->faker->optional()->randomFloat(2, 5000, 100000),
            'advance_rental' => $this->faker->optional()->randomFloat(2, 5000, 100000),
            'application_of_advance' => $this->faker->randomElement(ApplicationOfAdvance::cases()),
            'min_lease' => $minLease,
            'max_lease' => $maxLease,
            'escalation_rate' => $shouldEscalate ? $this->faker->randomFloat(2, 3, 8) : null,
            'escalation_frequency' => $shouldEscalate ? $this->faker->randomElement(EscalationFrequency::cases()) : null,
            'escalation_effectivity' => $shouldEscalate ? $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d') : null,
            'remarks' => $this->faker->optional(0.5)->paragraph(),
        ];
    }
}
