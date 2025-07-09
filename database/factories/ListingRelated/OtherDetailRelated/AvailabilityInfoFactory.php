<?php

namespace Database\Factories\ListingRelated\OtherDetailRelated;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ListingRelated\OtherDetailRelated\OtherDetail;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\OtherDetailRelated\AvailabilityInfo>
 */
class AvailabilityInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'other_detail_id' => OtherDetail::factory(),
            'date_of_availability' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'date_of_availability_remarks' => $this->faker->optional(0.8)->sentence(), // 80% chance remarks
        ];
    }
}
