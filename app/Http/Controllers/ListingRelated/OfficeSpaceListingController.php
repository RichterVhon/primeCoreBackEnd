<?php

namespace App\Http\Controllers\ListingRelated;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\HandlesListingCreation;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Http\Requests\StoreOfficeSpaceListingRequest;
use App\Http\Requests\UpdateOfficeSpaceListingRequest;

class OfficeSpaceListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = OfficeSpaceListing::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), OfficeSpaceListing::searchableFields());
        }

        $query->applyFilters($request->only(OfficeSpaceListing::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $Offices = $query
            ->with([
                'listing.account',
                'listing.location',
                'listing.inquiries',
                'listing.contacts',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',

                'OfficeListingPropertyDetails',
                'OfficeTurnoverConditions',
                'OfficeSpecs',
                // 'OfficeLeaseTermsAndConditionsExtn',
                // 'OfficeOtherDetailExtn',
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $Offices->items(),
            'meta' => [
                'current_page' => $Offices->currentPage(),
                'per_page' => $Offices->perPage(),
                'total' => $Offices->total(),
                'last_page' => $Offices->lastPage(),
                'next_page_url' => $Offices->nextPageUrl(),
                'prev_page_url' => $Offices->previousPageUrl()
            ]
        ]);
    }

    use HandlesListingCreation;

    public function show($id): JsonResponse
    {
        $Office = OfficeSpaceListing::with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'OfficeListingPropertyDetails',
            'OfficeTurnoverConditions',
            'OfficeSpecs',
            'OfficeLeaseTermsAndConditionsExtn',
            'OfficeOtherDetailExtn',
        ])->findOrFail($id);

        return response()->json(['data' => $Office]);
    }

    public function store(StoreOfficeSpaceListingRequest $request): JsonResponse
    {
        $officeSpace = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Create office listing morph target
            $officeSpace = OfficeSpaceListing::create([
                // 'PEZA_accredited' => $data['PEZA_accredited'] ?? null // if applicable
            ]);

            // Create listing + attach morph
            $listing = $this->createListing($data['listing'], $officeSpace);

            // 📎 Add nested core listing components
            $this->createListingComponents($listing, $data['listing']);
            $otherDetail = $listing->otherDetail;
            $leaseterms = $listing->leaseTermsAndConditions;
            // 🔗 Add office-specific components
            $officeSpace->officeSpecs()->create($data['office_specs'] ?? []);
            $officeSpace->officeTurnoverConditions()->create($data['office_turnover_conditions'] ?? []);
            $officeSpace->officeListingPropertyDetails()->create($data['office_listing_property_details'] ?? []);
            $officeSpace->officeOtherDetailExtn()->create(array_merge(
                $data['office_other_detail_extn'] ?? [],
                ['other_detail_id' => $otherDetail->id]
            ));
            $officeSpace->officeLeaseTermsAndConditionsExtn()->create(array_merge(
                $data['office_lease_terms_extn'] ?? [],
                ['lease_terms_and_conditions_id' => $leaseterms->id]
            ));

            return $officeSpace;
        });

        // ⏪ Fetch full relationship tree
        $fullOfficeSpace = OfficeSpaceListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'officeSpecs',
            'officeTurnoverConditions',
            'officeListingPropertyDetails',
            'officeOtherDetailExtn',
            'officeLeaseTermsAndConditionsExtn',
        ])->findOrFail($officeSpace->id);

        return response()->json([
            'message' => 'Office listing successfully created with all components.',
            'data' => $fullOfficeSpace
        ], 201);
    }

    public function update(UpdateOfficeSpaceListingRequest $request, $id): JsonResponse
    {
        $Office = OfficeSpaceListing::with([
            'listing',
            'OfficeListingPropertyDetails',
            'OfficeTurnoverConditions',
            'OfficeSpecs',
            'OfficeLeaseTermsAndConditionsExtn',
            'OfficeOtherDetailExtn',
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($Office, $data) {

            // Update the already existing listing fields
            $this->updateListing($Office->listing, $data['listing'] ?? []);

            // Update for its components
            $this->updateListingComponents($Office->listing, $data['listing'] ?? []);

            // Update Office components
            $Office->officeSpecs()->update($data['office_specs'] ?? []);
            $Office->officeTurnoverConditions()->update($data['office_turnover_conditions'] ?? []);
            $Office->officeListingPropertyDetails()->update($data['office_listing_property_details'] ?? []);
            $Office->officeOtherDetailExtn()->update($data['office_other_detail_extn'] ?? []);
            $Office->officeLeaseTermsAndConditionsExtn()->update($data['office_lease_terms_extn'] ?? []);


            // $Office->officespaceListingPropDetails()->update($data['officespace_listing_prop_details'] ?? []);
            // $Office->officespaceTurnoverConditions()->update($data['officespace_turnover_conditions'] ?? []);
            // $Office->officespaceSpecs()->update($data['officespace_specs'] ?? []);
            // $Office->officespaceLeaseRate()->update($data['officespace_lease_rates'] ?? []);
        });

        // 🧾 Return fully refreshed listing with all relationships
        $updated = officespaceListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'officeSpecs',
            'officeTurnoverConditions',
            'officeListingPropertyDetails',
            'officeOtherDetailExtn',
            'officeLeaseTermsAndConditionsExtn',
        ])->findOrFail($Office->id);

        return response()->json([
            'message' => 'Office listing successfully updated.',
            'data' => $updated
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        $Office = OfficeSpaceListing::with([
            'listing',
            'OfficeLeaseTermsAndConditionsExtn',
            'OfficeTurnoverConditions',
            'OfficeSpecs',
            'OfficeOtherDetailExtn',
            'OfficeListingPropertyDetails'
        ])->findOrFail($id);

        DB::transaction(function () use ($Office) {
            $Office->delete(); // triggers soft deletes via model event
        });

        return response()->json([
            'message' => 'Office space listing and related data successfully soft deleted.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $Office = OfficeSpaceListing::withTrashed()->with([
            'listing',
            'OfficeLeaseTermsAndConditionsExtn',
            'OfficeTurnoverConditions',
            'OfficeSpecs',
            'OfficeOtherDetailExtn',
            'OfficeListingPropertyDetails'
        ])->findOrFail($id);

        if (!$Office->trashed()) {
            return response()->json([
                'message' => 'Office Space listing is not deleted and cannot be restored.'
            ], 400);
        }


        DB::transaction(function () use ($Office) {
            $Office->restoreCascade();
        });

        return response()->json([
            'message' => 'Office space listing and related data successfully restored.'
        ]);
    }


}
