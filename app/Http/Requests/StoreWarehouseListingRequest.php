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

class StoreWarehouseListingRequest extends FormRequest
{
    use HasListingValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->listingRules(), 

            [
                // 🏢 Warehouse Listings table
                'peza_accredited' => ['required', new Enum(AccreditationType::class)],

                // 📦 warehouse_listing_prop_details table
                'warehouse_listing_prop_details.unit_number' => 'nullable|string',
                'warehouse_listing_prop_details.leasable_warehouse_area_on_the_ground_floor' => 'nullable|numeric',
                'warehouse_listing_prop_details.leasable_warehouse_area_on_the_upper_floor' => 'nullable|numeric',
                'warehouse_listing_prop_details.leasable_office_area' => 'nullable|numeric',
                'warehouse_listing_prop_details.total_leasable_area' => 'nullable|numeric',
                'warehouse_listing_prop_details.total_open_area' => 'nullable|numeric',
                'warehouse_listing_prop_details.total_leasable_area_open_covered' => 'nullable|numeric',
                'warehouse_listing_prop_details.FDAS' => ['required', new Enum(YesNo::class)],

                // 🧱 warehouse_turnover_conditions table
                'warehouse_turnover_conditions.flooring_turnover' => 'nullable|string',
                'warehouse_turnover_conditions.ceiling_turnover' => 'nullable|string',
                'warehouse_turnover_conditions.wall_turnover' => 'nullable|string',
                'warehouse_turnover_conditions.turnover_remarks' => 'nullable|string',

                // ⚙️ warehouse_specs table
                'warehouse_specs.application_of_cusa' => 'nullable|string',
                'warehouse_specs.apex' => 'nullable|numeric',
                'warehouse_specs.shoulder_height' => 'nullable|numeric',
                'warehouse_specs.dimensions_of_the_entrance' => 'nullable|numeric',
                'warehouse_specs.parking_allotment' => 'nullable|integer',
                'warehouse_specs.loading_bay' => 'nullable|integer',
                'warehouse_specs.loading_bay_elevation' => 'nullable|numeric',
                'warehouse_specs.type_of_loading_bay' => ['required', new Enum(TypeOfLoadingBay::class)],
                'warehouse_specs.loading_bay_vehicular_capacity' => ['required', new Enum(VehicleCapacity::class)],
                'warehouse_specs.electrical_load_capacity' => ['required', new Enum(ElectricalLoadCapacity::class)],
                'warehouse_specs.vehicle_capacity' => ['required', new Enum(VehicleCapacity::class)],
                'warehouse_specs.concrete_floor_strength' => 'nullable|numeric',
                'warehouse_specs.parking_rate_slot' => 'nullable|numeric',

                // 💰 warehouse_lease_rates table
                'warehouse_lease_rates.rental_rate_sqm_for_open_area' => 'nullable|numeric',
                'warehouse_lease_rates.rental_rate_sqm_for_covered_warehouse_area' => 'nullable|numeric',
                'warehouse_lease_rates.rental_rate_sqm_for_office_area' => 'nullable|numeric',
            ]
        );
    }
}
