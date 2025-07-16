<?php

namespace App\Http\Requests;

use App\Enums\Toilets;
use App\Enums\HandoverType;
use App\Enums\GenSetProvision;
use App\Enums\BackUpPowerOption;
use App\Enums\Pylonavailability;
use Illuminate\Validation\Rules\Enum;
use App\Traits\HasListingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRetailOfficeListingRequest extends FormRequest
{
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

    use HasListingValidationRules;
    public function rules(): array
    {
        return array_merge(
            $this->listingRules(),
            [
                // 🧱 retail_office_turnover_conditions table
                'retail_office_turnover_conditions.frontage_turnover' => 'required|string',
                'retail_office_turnover_conditions.turnover_remarks' => 'nullable|string',

                // 🏢 retail_office_listing_property_details table
                'retail_office_listing_property_details.floor_level' => 'nullable|string',
                'retail_office_listing_property_details.unit_number' => 'nullable|string',
                'retail_office_listing_property_details.leasable_size' => 'nullable|numeric',

                // ⚙️ retail_office_building_specs table
                'retail_office_building_specs.PSI' => 'nullable|numeric',
                'retail_office_building_specs.handover' => ['required', new Enum(HandoverType::class)],
                'retail_office_building_specs.ceiling' => 'nullable|string',
                'retail_office_building_specs.wall' => 'nullable|string',
                'retail_office_building_specs.floor' => 'nullable|string',
                'retail_office_building_specs.building_ops' => 'nullable|string',
                'retail_office_building_specs.backup_power' => ['required', new Enum(BackUpPowerOption::class)],
                'retail_office_building_specs.provision_for_genset' => ['required', new Enum(GenSetProvision::class)],
                'retail_office_building_specs.security_system' => 'nullable|string',
                'retail_office_building_specs.telecom_providers' => 'nullable|string',
                'retail_office_building_specs.passenger_elevators' => 'nullable|integer',
                'retail_office_building_specs.service_elevators' => 'nullable|integer',
                'retail_office_building_specs.drainage_provision' => ['required', new Enum(GenSetProvision::class)],
                'retail_office_building_specs.sewage_treatment_plan' => ['required', new Enum(GenSetProvision::class)],
                'retail_office_building_specs.plumbing_provision' => ['required', new Enum(GenSetProvision::class)],
                'retail_office_building_specs.toilet' => ['required', new Enum(Toilets::class)],
                'retail_office_building_specs.tenant_restrictions' => 'nullable|string',
                'retail_office_building_specs.parking_rate_slot' => 'nullable|numeric',
                'retail_office_building_specs.parking_rate_allotment' => 'nullable|numeric',
                'retail_office_building_specs.floor_to_ceiling_height' => 'nullable|numeric',
                'retail_office_building_specs.floor_to_floor_height' => 'nullable|numeric',
                'retail_office_building_specs.mezzanine' => 'nullable|numeric',

                // 🧩 retail_office_other_detail_extns table
                'retail_office_other_detail_extn.pylon_availability' => ['required', new Enum(Pylonavailability::class)],
                'retail_office_other_detail_extn.total_floor_count' => 'nullable|integer',
                'retail_office_other_detail_extn.other_remarks' => 'nullable|string',
            ]
        );
    }
}
