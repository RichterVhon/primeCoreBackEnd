<?php

namespace App\Traits;

use Illuminate\Validation\Rules\Enum;
use App\Enums\ListingStatus;
use App\Enums\AuthorityType;
use App\Enums\Province;
use App\Enums\ApplicationOfAdvance;
use App\Enums\EscalationFrequency;
use App\Enums\Meter;

trait HasListingValidationRules
{
    public function listingRules(): array
    {  
        return [
            // 🧍 Core Listing
            'listing.status' => ['required', new Enum(ListingStatus::class)],
            'listing.project_name' => 'required|string',
            'listing.property_name' => 'required|string',
            'listing.bd_incharge' => 'required|string',
            'listing.authority_type' => ['required', new Enum(AuthorityType::class)],
            'listing.bd_securing_remarks' => 'nullable|string',

            // 📍 Location
            'listing.location.province' => ['required', new Enum(Province::class)],
            'listing.location.city' => 'required|string',
            'listing.location.district' => 'required|string',
            'listing.location.google_coordinates_latitude' => 'required|numeric',
            'listing.location.google_coordinates_longitude' => 'required|numeric',
            'listing.location.exact_address' => 'required|string',

            // 📄 Lease Document
            'listing.lease_document.photos_and_property_documents_link' => 'nullable|string',
            'listing.lease_document.professional_fee_structure' => 'nullable|string',

            // 🧾 Lease Terms and Conditions
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

            // 🛠️ Other Detail
            'listing.other_detail.electricity_meter' => ['required', new Enum(Meter::class)],
            'listing.other_detail.water_meter' => ['required', new Enum(Meter::class)],
            'listing.other_detail.year_built' => 'nullable|integer',

            // 🧑‍💼 Contacts
            'listing.contacts' => 'nullable|array',
            'listing.contacts.*.contact_id' => 'required|exists:contacts,id',
            'listing.contacts.*.company' => 'nullable|string',
        ];
    }

    public function listingRulesForUpdate(): array
    {
    return [
            'listing.status' => ['sometimes', new Enum(ListingStatus::class)],
            'listing.project_name' => 'sometimes|string',
            'listing.property_name' => 'sometimes|string',
            'listing.bd_incharge' => 'sometimes|string',
            'listing.authority_type' => ['sometimes', new Enum(AuthorityType::class)],
            'listing.bd_securing_remarks' => 'nullable|string',

            'listing.location.province' => ['sometimes', new Enum(Province::class)],
            'listing.location.city' => 'sometimes|string',
            'listing.location.district' => 'sometimes|string',
            'listing.location.google_coordinates_latitude' => 'sometimes|numeric',
            'listing.location.google_coordinates_longitude' => 'sometimes|numeric',
            'listing.location.exact_address' => 'sometimes|string',

            'listing.lease_document.photos_and_property_documents_link' => 'nullable|string',
            'listing.lease_document.professional_fee_structure' => 'nullable|string',

            'listing.lease_terms_and_conditions.monthly_rate' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.cusa_sqm' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.security_deposit' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.advance_rental' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.application_of_advance' => ['sometimes', new Enum(ApplicationOfAdvance::class)],
            'listing.lease_terms_and_conditions.min_lease' => 'nullable|integer',
            'listing.lease_terms_and_conditions.max_lease' => 'nullable|integer',
            'listing.lease_terms_and_conditions.escalation_rate' => 'nullable|numeric',
            'listing.lease_terms_and_conditions.escalation_frequency' => ['sometimes', new Enum(EscalationFrequency::class)],
            'listing.lease_terms_and_conditions.escalation_effectivity' => 'nullable|date',
            'listing.lease_terms_and_conditions.remarks' => 'nullable|string',

            'listing.other_detail.electricity_meter' => ['sometimes', new Enum(Meter::class)],
            'listing.other_detail.water_meter' => ['sometimes', new Enum(Meter::class)],
            'listing.other_detail.year_built' => 'nullable|integer',

            'listing.contacts' => 'nullable|array',
            'listing.contacts.*.contact_id' => 'required|exists:contacts,id',
            'listing.contacts.*.company' => 'nullable|string',
        ];
    }

}
