<?php

namespace App\Http\Requests;

use App\Enums\AccreditationType;
use App\Enums\AcUnitType;
use App\Enums\BackupPowerType;
use App\Enums\FiberOpticCapability;
use App\Enums\HandoverType;
use App\Enums\TaxOnCusa;
use App\Enums\ToiletType;
use Illuminate\Validation\Rules\Enum;
use App\Traits\HasListingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeSpaceListingRequest extends FormRequest
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
            $this->listingRulesForUpdate(),
            [

                // office_lease_terms_and_conditions_extns table
                'office_lease_terms_extn.tax_on_cusa' => ['sometimes', new Enum(TaxOnCusa::class)],
                'office_lease_terms_extn.cusa_on_parking' => 'required|numeric|min:0',
                'office_lease_terms_extn.parking_rate_slot' => 'required|numeric|min:0',
                'office_lease_terms_extn.parking_allotment' => 'required|integer|min:0',

                // office_turnover_conditions table
                'office_turnover_conditions.handover' => ['sometimes', new Enum(HandoverType::class)], // enum later
                'office_turnover_conditions.ceiling' => 'nullable|string',
                'office_turnover_conditions.wall' => 'nullable|string',
                'office_turnover_conditions.floor' => 'nullable|string',
                'office_turnover_conditions.turnover_remarks' => 'nullable|string',

                // office_specs table
                'office_specs.density_ratio' => 'nullable|string',
                'office_specs.floor_to_ceiling_height' => 'nullable|numeric|min:0',
                'office_specs.floor_to_floor' => 'nullable|numeric|min:0',
                'office_specs.accreditation' => ['sometimes', new Enum(AccreditationType::class)],
                'office_specs.certification' => 'nullable|string',

                // office_other_details_extn table
                'office_other_detail_extn.a/c_unit' => ['sometimes', new Enum(AcUnitType::class)],
                'office_other_detail_extn.a/c_type' => 'nullable|string',
                'office_other_detail_extn.a/c_rate' => 'nullable|numeric|min:0',
                'office_other_detail_extn.cusa_on_ac' => 'nullable|numeric|min:0',
                'office_other_detail_extn.building_ops' => 'nullable|string',
                'office_other_detail_extn.backup_power' => ['sometimes', new Enum(BackupPowerType::class)],
                'office_other_detail_extn.fiber_optic_capability' => ['sometimes', new Enum(FiberOpticCapability::class)],
                'office_other_detail_extn.telecom_providers' => 'nullable|string',
                'office_other_detail_extn.passenger_elevators' => 'nullable|integer|min:0',
                'office_other_detail_extn.service_elevators' => 'nullable|integer|min:0',
                'office_other_detail_extn.private_toilet' => ['sometimes', new Enum(ToiletType::class)],
                'office_other_detail_extn.common_toilet' => ['sometimes', new Enum(ToiletType::class)],
                'office_other_detail_extn.tenant_restrictions' => 'nullable|string',
                'office_other_detail_extn.year_built' => 'nullable|string',
                'office_other_detail_extn.total_floor_count' => 'nullable|integer|min:1',
                'office_other_detail_extn.other_remarks' => 'nullable|string',

                // office_listing_property_details table
                'office_listing_property_details.floor_level' => 'nullable|string',
                'office_listing_property_details.unit_number' => 'nullable|string',
                'office_listing_property_details.leasable_size' => 'nullable|numeric|min:0',
            ]
        );
    }
}
