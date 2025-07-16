<?php

namespace App\Http\Requests;

use App\Enums\AccreditationType;
use App\Enums\LotShape;
use App\Enums\Offering;
use App\Enums\LotCondition;
use App\Enums\ZoningClassification;
use Illuminate\Validation\Rules\Enum;
use App\Traits\HasListingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreIndLotListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    use HasListingValidationRules;
    
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
        $this->listingRules(), // Assuming this includes base rules for ind_lot_listings
        [
            'PEZA_accredited' => ['required', new Enum(AccreditationType::class)],
            // 🧱 ind_lot_listing_property_details table
            'ind_lot_listing_property_details.lot_area' => 'nullable|numeric',
            'ind_lot_listing_property_details.lot_shape' => ['nullable', new Enum(LotShape::class)],
            'ind_lot_listing_property_details.frontage_width' => 'nullable|numeric',
            'ind_lot_listing_property_details.depth' => 'nullable|numeric',
            'ind_lot_listing_property_details.zoning_classification' =>  ['nullable', new Enum(ZoningClassification::class)],
            'ind_lot_listing_property_details.offering' =>  ['nullable', new Enum(Offering::class)], // Replace with Enum later if needed

            // 🧱 ind_lot_turnover_conditions table 
            'ind_lot_turnover_conditions.lot_condition' =>  ['nullable', new Enum(LotCondition::class)], // Replace with Enum later if needed
            'ind_lot_turnover_conditions.turnover_remarks' => 'nullable|string',

            // 💰 ind_lot_lease_rates table
            'ind_lot_lease_rates.rental_rate_sqm_for_open_area' => 'nullable|numeric',
            'ind_lot_lease_rates.rental_rate_sqm_for_covered_area' => 'nullable|numeric',
        ]
    );
}

}
