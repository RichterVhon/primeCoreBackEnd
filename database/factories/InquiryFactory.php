<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\Account;
use App\Models\ListingRelated\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => Account::factory(), // the agent who logs the inquiry
            'client_id' => Account::factory(), // the viewer/client
            'listing_id' => Listing::factory(),
            'status' => $this->faker->randomElement(InquiryStatus::cases()),
            'message' => $this->faker->paragraph,
            'viewing_schedule' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            //'agent_in_charge' => $this->faker->name
        ];
    }
}
