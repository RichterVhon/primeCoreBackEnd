<?php

namespace Database\Factories\ListingRelated\OtherDetailRelated;

use App\Enums\IdealUse;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ListingRelated\OtherDetailRelated\OtherDetail;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\OtherDetailRelated\TenantUsePolicy>
 */
class TenantUsePolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $restrictions = [
            'No heavy machinery allowed',
            'No pets permitted',
            'Residential tenants only',
            'Strict noise control policy',
            'Business hours access only',
        ];

        /*
        $uses = [
            'Ideal for co-working space',
            'Great for small retail',
            'Suited for medical clinics',
            'Perfect for residential lease',
            'Compatible with warehouse use',
        ];
        */

        return [
            'other_detail_id' => OtherDetail::factory(),
            'tenant_restrictions' => $this->faker->optional(0.8)->randomElement($restrictions),
            'ideal_use' => $this->faker->optional(0.8)->randomElement(IdealUse::cases()),
        ];
    }
}
