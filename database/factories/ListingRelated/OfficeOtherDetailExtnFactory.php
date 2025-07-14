<?php

namespace Database\Factories\ListingRelated;

use App\Enums\AcUnitType;
use App\Enums\ToiletType;
use App\Enums\BackupPowerType;
use App\Enums\FiberOpticCapability;
use App\Models\ListingRelated\OfficeSpaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ListingRelated\OfficeOtherDetailExtn;
use App\Models\ListingRelated\OtherDetailRelated\OtherDetail;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfficeOtherDetailExtn>
 */
class OfficeOtherDetailExtnFactory extends Factory
{

    protected $model = OfficeOtherDetailExtn::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $otherDetail = OtherDetail::factory()->create();

        return [
        'other_detail_id' => $otherDetail->id,
        'a/c_unit' => $this->faker->randomElement(AcUnitType::cases()),
        'a/c_type' => $this->faker->randomElement(['Split-Type','Floor Mounted', 'Window AC']),
        'a/c_rate' => $this->faker->randomFloat(2, 100, 500),
        'cusa_on_ac' => $this->faker->randomFloat(2, 50, 300),
        'building_ops' => $this->faker->company(),
        'backup_power' => $this->faker->randomElement(BackupPowerType::cases()),
        'fiber_optic_capability' => $this->faker->randomElement(FiberOpticCapability::cases()),
        'telecom_providers' => $this->faker->company(),
        'passenger_elevators' => $this->faker->numberBetween(1, 10),
        'service_elevators' => $this->faker->numberBetween(0, 3),
        'private_toilet' => $this->faker->randomElement(ToiletType::cases()),
        'common_toilet' => $this->faker->randomElement(ToiletType::cases()),
        'tenant_restrictions' => $this->faker->sentence(),
        'year_built' => $this->faker->year(),
        'total_floor_count' => $this->faker->numberBetween(1, 50),
        'other_remarks' => $this->faker->paragraph(),
        'electric_meter' => $otherDetail->electricity_meter->value,
        'water_meter' => $otherDetail->water_meter-> values,
        'office_space_listing_id' => OfficeSpaceListing::factory(),
        ];
    }
}
