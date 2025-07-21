<?php

namespace App\Http\Controllers\ListingRelated;

use App\Models\Contact;
use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesListingCreation;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ListingRelated\RetailOfficeListing;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\StoreRetailOfficeListingRequest;
use App\Http\Requests\UpdateRetailOfficeListingRequest;

class RetailOfficeListingController extends Controller
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

        $query = RetailOfficeListing::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), RetailOfficeListing::searchableFields());
        }

        $rawQuery = $request->query();
        $filterable = RetailOfficeListing::filterableFields();
        $filters = [];

        foreach ($rawQuery as $key => $value) {
            if (in_array($key, $filterable)) {
                $filters[$key] = $value;
                continue;
            }

            foreach ($filterable as $filterKey) {
                $normalized = str_replace('.', '_', $filterKey);
                if ($normalized === $key) {
                    $filters[$filterKey] = $value;
                    break;
                }
            }
        }

        $query->applyFilters($filters);
        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $retailListings = $query
            ->with([
                'listing.location',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',
                'listing.contacts',
                'listing.inquiries',
                'retailOfficeTurnoverConditions',
                'retailOfficeListingPropertyDetails',
                'retailOfficeBuildingSpecs',
                'retailOfficeOtherDetailExtn'
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $retailListings->items(),
            'meta' => [
                'current_page' => $retailListings->currentPage(),
                'per_page' => $retailListings->perPage(),
                'total' => $retailListings->total(),
                'last_page' => $retailListings->lastPage(),
                'next_page_url' => $retailListings->nextPageUrl(),
                'prev_page_url' => $retailListings->previousPageUrl()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $retailoffice = RetailOfficeListing::withTrashed()->with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'RetailOfficeListingPropertyDetails',
            'RetailOfficeTurnoverConditions',
            'RetailOfficeBuildingSpecs',
            'RetailOfficeOtherDetailExtn',

        ])->find($id);

        if ($retailoffice->trashed()) {
            return response()->json([
                'message' => "Retail Office Listing with ID {$id} has been deleted."
            ], 410); // 410 Gone is semantically accurate
        }

        if (!$retailoffice) {
            return response()->json([
                'message' => "Retail Office Listing with ID {$id} does not exist."
            ]);
        }

        return response()->json(['data' => $retailoffice]);
    }

    use HandlesListingCreation;

    public function store(StoreRetailOfficeListingRequest $request): JsonResponse
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

        $retailOffice = DB::transaction(function () use ($request, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $data = $request->validated();

            $retailOffice = RetailOfficeListing::create();

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

            $listing = $this->createListing($data['listing'], $retailOffice);
            $this->createListingComponents($listing, $data['listing']);

            $retailOffice->retailOfficeListingPropertyDetails()->create($data['retail_office_listing_property_details'] ?? []);
            $retailOffice->retailOfficeTurnoverConditions()->create($data['retail_office_turnover_conditions'] ?? []);
            $retailOffice->retailOfficeBuildingSpecs()->create($data['retail_office_building_specs'] ?? []);
            $retailOffice->retailOfficeOtherDetailExtn()->create(array_merge(
                $data['retail_office_other_detail_extn'] ?? [],
                ['other_detail_id' => $listing->otherDetail->id]
            ));

            $listingRedirectUrl = route('retailoffice.show', ['id' => $retailOffice->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };

            return $retailOffice;
        });

        $fullRetailOffice = RetailOfficeListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'retailOfficeListingPropertyDetails',
            'retailOfficeTurnoverConditions',
            'retailOfficeBuildingSpecs',
            'retailOfficeOtherDetailExtn'
        ])->findOrFail($retailOffice->id);

        return response()->json([
            'message' => 'Retail office listing successfully created.',
            'data' => $fullRetailOffice,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 201);
    }

    public function update(UpdateRetailOfficeListingRequest $request, $id): JsonResponse
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

        $retailOffice = RetailOfficeListing::with([
            'listing',
            'retailOfficeListingPropertyDetails',
            'retailOfficeTurnoverConditions',
            'retailOfficeBuildingSpecs',
            'retailOfficeOtherDetailExtn'
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($retailOffice, $data, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
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

            $this->updateListing($retailOffice->listing, $data['listing'] ?? []);
            $this->updateListingComponents($retailOffice->listing, $data['listing'] ?? []);

            $retailOffice->retailOfficeListingPropertyDetails()->update($data['retail_office_listing_property_details'] ?? []);
            $retailOffice->retailOfficeTurnoverConditions()->update($data['retail_office_turnover_conditions'] ?? []);
            $retailOffice->retailOfficeBuildingSpecs()->update($data['retail_office_building_specs'] ?? []);
            $retailOffice->retailOfficeOtherDetailExtn()->update($data['retail_office_other_detail_extn'] ?? []);

            $listingRedirectUrl = route('retailoffice.show', ['id' => $retailOffice->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };
        });

        $updated = RetailOfficeListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'retailOfficeListingPropertyDetails',
            'retailOfficeTurnoverConditions',
            'retailOfficeBuildingSpecs',
            'retailOfficeOtherDetailExtn'
        ])->findOrFail($retailOffice->id);

        return response()->json([
            'message' => 'Retail office listing successfully updated.',
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
        $retailoffice = RetailOfficeListing::with([
            'listing',
            'retailOfficeTurnoverConditions',
            'retailOfficeListingPropertyDetails',
            'retailOfficeBuildingSpecs',
            'retailOfficeOtherDetailExtn'
        ])->findOrFail($id);

        DB::transaction(function () use ($retailoffice) {
            $retailoffice->delete(); // triggers soft deletes via model event
        });

        return response()->json([
            'message' => 'Retail office listing and related data successfully soft deleted.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $retailoffice = RetailOfficeListing::withTrashed()->with([
            'listing',
            'retailOfficeTurnoverConditions',
            'retailOfficeListingPropertyDetails',
            'retailOfficeBuildingSpecs',
            'retailOfficeOtherDetailExtn',
        ])->findOrFail($id);

        if (!$retailoffice->trashed()) {
            return response()->json([
                'message' => 'Retail office listing is not deleted and cannot be restored.'
            ], 400);
        }

        DB::transaction(function () use ($retailoffice) {
            $retailoffice->restoreCascade();
        });

        return response()->json([
            'message' => 'Retail office listing and related data successfully restored.'
        ]);
    }

}
