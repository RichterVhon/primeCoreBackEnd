<?php

namespace App\Traits;

use App\Models\ListingRelated\Listing;
use Illuminate\Support\Facades\Auth;

trait HandlesListingCreation
{
    public function createListing(array $listingData, $listable): Listing
    {
        $listingData['account_id'] = Auth::id();
        $listingData['date_uploaded'] = now();
        $listingData['date_last_updated'] = now();

        $listing = new Listing($listingData);
        $listing->listable()->associate($listable);
        $listing->save();

        return $listing;
    }

    public function createListingComponents(Listing $listing, array $data): void
    {
        $listing->location()->create($data['location'] ?? []);
        $listing->leaseDocument()->create($data['lease_document'] ?? []);
        $listing->leaseTermsAndConditions()->create($data['lease_terms_and_conditions'] ?? []);
        $listing->otherDetail()->create($data['other_detail'] ?? []);

        foreach ($data['contacts'] ?? [] as $contact) {
            $listing->contacts()->attach($contact['contact_id'], [
                'company' => $contact['company'] ?? null,
            ]);
        }
    }
}
