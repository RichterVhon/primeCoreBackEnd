<?php

namespace App\Http\Requests;

use App\Enums\AccreditationType;
use App\Enums\ApplicationOfAdvance;
use App\Enums\AuthorityType;
use App\Enums\ElectricalLoadCapacity;
use App\Enums\EscalationFrequency;
use App\Enums\ListingStatus;
use App\Enums\Meter;
use App\Enums\Province;
use App\Enums\TypeOfLoadingBay;
use App\Enums\VehicleCapacity;
use App\Enums\YesNo;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseListingRequest extends FormRequest
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
public function rules(): array
{
    return [
            // 🧍 Listing core fields
            //'listing.account_id' => 'required|exists:accounts,id',
            'listing.status' => ['required', new Enum(ListingStatus::class)],
            'listing.project_name' => 'required|string',
            'listing.property_name' => 'required|string',
            'listing.bd_incharge' => 'required|string',
            'listing.authority_type' => ['required', new Enum(AuthorityType::class)],
            'listing.bd_securing_remarks' => 'nullable|string',

            // 📍 Location (locations table)
            'listing.location.province' => ['required', new Enum(Province::class)],
            'listing.location.city' => 'required|string',
            'listing.location.district' => 'required|string',
            'listing.location.google_coordinates_latitude' => 'required|numeric',
            'listing.location.google_coordinates_longitude' => 'required|numeric',
            'listing.location.exact_address' => 'required|string',

            // 📄 Lease Document (lease_documents table)
            'listing.lease_document.photos_and_property_documents_link' => 'nullable|string',
            'listing.lease_document.professional_fee_structure' => 'nullable|string',

            // 🧾 Lease Terms and Conditions (lease_terms_and_conditions table)
            'listing.lease_terms_and_conditions.monthly_rate' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.cusa_sqm' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.security_deposit' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.advance_rental' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.application_of_advance' => ['required', new Enum(ApplicationOfAdvance::class)],
            'listing.lease_terms_and_conditions.min_lease' => 'nullable|integer',
            'listing.lease_terms_and_conditions.max_lease' => 'nullable|integer',
            'listing.lease_terms_and_conditions.escalation_rate' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.escalation_frequency' => ['required', new Enum(EscalationFrequency::class)],
            'listing.lease_terms_and_conditions.escalation_effectivity' => 'nullable|date',
            'listing.lease_terms_and_conditions.remarks' => 'nullable|string',

            // 🛠️ Other Details (other_details table)
            'listing.other_detail.electricity_meter' => ['required', new Enum(Meter::class)],
            'listing.other_detail.water_meter' => ['required', new Enum(Meter::class)],
            'listing.other_detail.year_built' => 'nullable|integer',

            // 🧑‍💼 Contacts (contact_listings pivot)
            'listing.contacts' => 'nullable|array',
            'listing.contacts.*.contact_id' => 'required|exists:contacts,id',
            'listing.contacts.*.company' => 'nullable|string',

            // 🏢 Warehouse Listings table (morph target)
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
    ];
    }
}
