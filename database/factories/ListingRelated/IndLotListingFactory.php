<?php

namespace Database\Factories\ListingRelated;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\IndLotListing>
 */
class IndLotListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'PEZA_accredited' => $this->faker->boolean(60), //60% chance maging true
        ];
    }
}
