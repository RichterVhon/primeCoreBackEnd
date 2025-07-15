<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Traits\HasListingValidationRules;

use App\Enums\AccreditationType;
use App\Enums\TypeOfLoadingBay;
use App\Enums\VehicleCapacity;
use App\Enums\ElectricalLoadCapacity;
use App\Enums\YesNo;

class UpdateWarehouseListingRequest extends FormRequest
{
    use HasListingValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->listingRulesForUpdate(), // 👇 trait method tailored for updates

            [
                // 🏢 Warehouse Listings
                'peza_accredited' => ['sometimes', new Enum(AccreditationType::class)],

                // 📦 Prop Details
                'warehouse_listing_prop_details.unit_number' => 'nullable|string',
                'warehouse_listing_prop_details.leasable_warehouse_area_on_the_ground_floor' => 'nullable|numeric',
                'warehouse_listing_prop_details.leasable_warehouse_area_on_the_upper_floor' => 'nullable|numeric',
                'warehouse_listing_prop_details.leasable_office_area' => 'nullable|numeric',
                'warehouse_listing_prop_details.total_leasable_area' => 'nullable|numeric',
                'warehouse_listing_prop_details.total_open_area' => 'nullable|numeric',
                'warehouse_listing_prop_details.total_leasable_area_open_covered' => 'nullable|numeric',
                'warehouse_listing_prop_details.FDAS' => ['sometimes', new Enum(YesNo::class)],

                // 🧱 Turnover Conditions
                'warehouse_turnover_conditions.flooring_turnover' => 'nullable|string',
                'warehouse_turnover_conditions.ceiling_turnover' => 'nullable|string',
                'warehouse_turnover_conditions.wall_turnover' => 'nullable|string',
                'warehouse_turnover_conditions.turnover_remarks' => 'nullable|string',

                // ⚙️ Warehouse Specs
                'warehouse_specs.application_of_cusa' => 'nullable|string',
                'warehouse_specs.apex' => 'nullable|numeric',
                'warehouse_specs.shoulder_height' => 'nullable|numeric',
                'warehouse_specs.dimensions_of_the_entrance' => 'nullable|numeric',
                'warehouse_specs.parking_allotment' => 'nullable|integer',
                'warehouse_specs.loading_bay' => 'nullable|integer',
                'warehouse_specs.loading_bay_elevation' => 'nullable|numeric',
                'warehouse_specs.type_of_loading_bay' => ['sometimes', new Enum(TypeOfLoadingBay::class)],
                'warehouse_specs.loading_bay_vehicular_capacity' => ['sometimes', new Enum(VehicleCapacity::class)],
                'warehouse_specs.electrical_load_capacity' => ['sometimes', new Enum(ElectricalLoadCapacity::class)],
                'warehouse_specs.vehicle_capacity' => ['sometimes', new Enum(VehicleCapacity::class)],
                'warehouse_specs.concrete_floor_strength' => 'nullable|numeric',
                'warehouse_specs.parking_rate_slot' => 'nullable|numeric',

                // 💰 Lease Rates
                'warehouse_lease_rates.rental_rate_sqm_for_open_area' => 'nullable|numeric',
                'warehouse_lease_rates.rental_rate_sqm_for_covered_warehouse_area' => 'nullable|numeric',
                'warehouse_lease_rates.rental_rate_sqm_for_office_area' => 'nullable|numeric',
            ]
        );
    }
}
