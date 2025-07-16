<?php

namespace App\Http\Requests;

use App\Enums\LotShape;
use App\Enums\LotCondition;
use App\Enums\ZoningClassification;
use Illuminate\Validation\Rules\Enum;
use App\Traits\HasListingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommLotListingRequest extends FormRequest
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
            // 🧱 comm_lot_turnover_conditions table
            'comm_lot_turnover_conditions.lot_condition' => ['nullable', new Enum(LotCondition::class)], // can be updated to Enum later
            'comm_lot_turnover_conditions.turnover_remarks' => 'nullable|string',
            
            // 📐 comm_lot_listing_property_details table
            'comm_lot_listing_property_details.lot_area' => 'nullable|numeric',
            'comm_lot_listing_property_details.lot_shape' => ['nullable', new Enum(LotShape::class)],
            'comm_lot_listing_property_details.frontage_width' => 'nullable|numeric',
            'comm_lot_listing_property_details.depth' => 'nullable|numeric',
            'comm_lot_listing_property_details.zoning_classification' => ['nullable', new Enum(ZoningClassification::class)],
        ]
    );
}

}
