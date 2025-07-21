<?php

namespace App\Http\Controllers\ListingRelated;

use App\Models\Contact;
use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesListingCreation;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Http\Requests\StoreOfficeSpaceListingRequest;
use App\Http\Requests\UpdateOfficeSpaceListingRequest;

class OfficeSpaceListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (($user->role !== AccountRole::Agent) && ($user->role !== AccountRole::Admin)) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = OfficeSpaceListing::query();

        // 🔍 Search
        if ($request->filled('search')) {
            $query->search($request->input('search'), OfficeSpaceListing::searchableFields());
        }

        // 🧠 Filter normalization
        $rawQuery = $request->query();
        $filterable = OfficeSpaceListing::filterableFields();
        $filters = [];

        foreach ($rawQuery as $key => $value) {
            if (in_array($key, $filterable)) {
                $filters[$key] = $value;
                continue;
            }

            $matched = false;
            foreach ($filterable as $filterKey) {
                $normalized = str_replace('.', '_', $filterKey);
                if ($normalized === $key) {
                    $filters[$filterKey] = $value;
                    $matched = true;
                    break;
                }
            }
        }

        $query->applyFilters($filters);
        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $offices = $query
            ->with([
                'listing.location',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',
                'listing.contacts',
                'listing.inquiries',
                'OfficeLeaseTermsAndConditionsExtn',
                'OfficeTurnoverConditions',
                'OfficeSpecs',
                'OfficeOtherDetailExtn',
                'OfficeListingPropertyDetails'
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $offices->items(),
            'meta' => [
                'current_page' => $offices->currentPage(),
                'per_page' => $offices->perPage(),
                'total' => $offices->total(),
                'last_page' => $offices->lastPage(),
                'next_page_url' => $offices->nextPageUrl(),
                'prev_page_url' => $offices->previousPageUrl()
            ]
        ]);
    }
    

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

    use HandlesListingCreation;

    public function store(StoreOfficeSpaceListingRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (($user->role !== AccountRole::Agent) && ($user->role !== AccountRole::Admin)) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }

        $createdContacts = [];
        $listingRedirectUrl = null;
        $contactRedirectUrl = null;

        $officeSpace = DB::transaction(function () use ($request, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $data = $request->validated();

            $officeSpace = OfficeSpaceListing::create([
                'PEZA_accredited' => $data['PEZA_accredited'] ?? null
            ]);

            $pivotData = [];
            foreach ($data['listing']['contacts'] ?? [] as $entry) {
                if (!empty($entry['email'])) {
                    $contact = Contact::firstOrCreate(['email_address' => $entry['email']], []);
                    if ($contact->wasRecentlyCreated) {
                        $createdContacts[] = $contact;
                    }
                    $pivotData[$contact->id] = ['company' => $entry['company'] ?? null];
                }
            }

            $data['listing']['contacts'] = collect($pivotData)->map(fn($pivot, $contactId) => [
                'contact_id' => $contactId,
                'company' => $pivot['company']
            ])->values()->toArray();

            $listing = $this->createListing($data['listing'], $officeSpace);
            $this->createListingComponents($listing, $data['listing']);

            $officeSpace->officeSpecs()->create($data['office_specs'] ?? []);
            $officeSpace->officeTurnoverConditions()->create($data['office_turnover_conditions'] ?? []);
            $officeSpace->officeListingPropertyDetails()->create($data['office_listing_property_details'] ?? []);
            $officeSpace->officeOtherDetailExtn()->create(array_merge($data['office_other_detail_extn'] ?? [], [
                'other_detail_id' => $listing->otherDetail->id
            ]));
            $officeSpace->officeLeaseTermsAndConditionsExtn()->create(array_merge($data['office_lease_terms_extn'] ?? [], [
                'lease_terms_and_conditions_id' => $listing->leaseTermsAndConditions->id
            ]));

            $listingRedirectUrl = route('officespace.show', ['id' => $officeSpace->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };

            return $officeSpace;
        });

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
            'message' => 'Office listing successfully created.',
            'data' => $fullOfficeSpace,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 201);
    }

    public function update(UpdateOfficeSpaceListingRequest $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (($user->role !== AccountRole::Agent) && ($user->role !== AccountRole::Admin)) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }

        $createdContacts = [];
        $listingRedirectUrl = null;
        $contactRedirectUrl = null;

        $office = OfficeSpaceListing::with([
            'listing',
            'officeSpecs',
            'officeTurnoverConditions',
            'officeListingPropertyDetails',
            'officeOtherDetailExtn',
            'officeLeaseTermsAndConditionsExtn',
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($office, $data, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $pivotData = [];
            foreach ($data['listing']['contacts'] ?? [] as $entry) {
                if (!empty($entry['email'])) {
                    $contact = Contact::firstOrCreate(['email_address' => $entry['email']], []);
                    if ($contact->wasRecentlyCreated) {
                        $createdContacts[] = $contact;
                    }
                    $pivotData[$contact->id] = ['company' => $entry['company'] ?? null];
                }
            }

            $data['listing']['contacts'] = collect($pivotData)->map(fn($pivot, $contactId) => [
                'contact_id' => $contactId,
                'company' => $pivot['company']
            ])->values()->toArray();

            $this->updateListing($office->listing, $data['listing'] ?? []);
            $this->updateListingComponents($office->listing, $data['listing'] ?? []);

            $office->officeSpecs()->update($data['office_specs'] ?? []);
            $office->officeTurnoverConditions()->update($data['office_turnover_conditions'] ?? []);
            $office->officeListingPropertyDetails()->update($data['office_listing_property_details'] ?? []);
            $office->officeOtherDetailExtn()->update($data['office_other_detail_extn'] ?? []);
            $office->officeLeaseTermsAndConditionsExtn()->update($data['office_lease_terms_extn'] ?? []);

            $listingRedirectUrl = route('officespace.show', ['id' => $office->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };
        });

        $updated = OfficeSpaceListing::with([
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
        ])->findOrFail($office->id);

        return response()->json([
            'message' => 'Office listing successfully updated.',
            'data' => $updated,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 200);
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
