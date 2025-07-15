<?php

namespace Database\Factories\ListingRelated;

use App\Enums\BackUpPowerOption;
use App\Enums\GenSetProvision;
use App\Enums\HandoverType;
use App\Enums\Toilets;
use App\Models\ListingRelated\RetailOfficeListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListingRelated\RetailOfficeBuildingSpecs>
 */
class RetailOfficeBuildingSpecsFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buildingOpsOptions = [
            "24/7 Security and Maintenance",
            "Business Hours Only",
            "Fully Automated Building Management System (BMS)",
            "LEED-Certified Operations",
            "Green Building Practices",
            "Energy-Efficient HVAC Monitoring",
            "Centralized Waste Management",
            "On-Site Facilities Management",
            "Smart Access Control",
            "Integrated Fire Safety & Alarm System",
            "Scheduled Janitorial Services",
            "Shared Building Operations with Adjacent Tower",
            "Third-Party Property Management",
            "Owner-Managed Operations",
            "Responsive Engineering Team on Call",
        ];

        $tenantRestrictions = [
            "No heavy industrial operations",
            "No food or beverage businesses",
            "No medical or dental clinics",
            "No call centers or BPO operations",
            "No cryptocurrency mining",
            "No religious gatherings or churches",
            "No educational or training centers",
            "No subleasing without landlord approval",
            "No alcohol sales",
            "No hazardous material storage",
            "No 24/7 operations without prior consent",
            "No high foot traffic tenants (e.g., convenience stores)",
            "No gambling or lottery services",
            "No live animals or pet grooming",
            "No spas or massage parlors",
        ];

        return [

            'PSI' => $this->faker->randomFloat(2, 2500.0, 4000.0),
            'handover' => $this->faker->randomElement(HandoverType::cases()),
            'ceiling' => $this->faker->randomElement(['Acoustic', 'T-Bone']), //Pls replace with data required
            'floor' => $this->faker->randomElement(['Smooth Cement', 'Rough']), // (2)
            'wall' => $this->faker->randomElement(['Painted', 'Raw', 'Partitioned']), // (3)
            'building_ops' => $this->faker->randomElement($buildingOpsOptions),
            'backup_power' => $this->faker->randomElement(BackUpPowerOption::cases()),
            'provision_for_genset' => $this->faker->randomElement(GenSetProvision::cases()),
            'security_system' => $this->faker->random,
            'telecom_providers' => $this->faker->random,
            'passenger_elevators' => $this->faker->random,
            'service_elevators' => $this->faker->random,
            'drainage_provision' => $this->faker->randomElement(GenSetProvision::cases()),
            'sewage_treatment_plan' => $this->faker->randomElement(GenSetProvision::cases()),
            'plumbing_provision' => $this->faker->randomElement(GenSetProvision::cases()),
            'toilet' => $this->faker->randomElement(Toilets::cases()),
            'tenant_restrictions' => $this->faker->randomElement($tenantRestrictions),
            'parking_rate_slot' => $this->faker->randomFloat(2,5000.00,10000.00),
            'parking_rate_allotment' => $this->faker->randomFloat(2,5000.00,10000.00),
            'floor_to_ceiling_height' => $this->faker->randomFloat(2,4.0,5.0),
            'floor_to_floor_height' => $this->faker->randomFloat(2,3.0,4.0),
            'mezzanine' => $this->faker->randomFloat(2,5000.00,10000.00),
            'retail_office_listing_id' => RetailOfficeListing::factory(),
        ];
    }
}
