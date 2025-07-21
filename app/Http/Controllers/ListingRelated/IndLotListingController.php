<?php

namespace App\Http\Controllers\ListingRelated;

use App\Models\Contact;
use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesListingCreation;
use App\Models\ListingRelated\IndLotListing;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreIndLotListingRequest;
use App\Http\Requests\UpdateIndLotListingRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\StoreWarehouseListingRequest;

class IndLotListingController extends Controller
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

        $query = IndLotListing::query();

        // 🔍 Search
        if ($request->filled('search')) {
            $query->search($request->input('search'), IndLotListing::searchableFields());
        }

        // 🧠 Filter normalization
        $rawQuery = $request->query();
        $filterable = IndLotListing::filterableFields();
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

        $indlots = $query
            ->with([
                'listing.location',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',
                'listing.contacts',
                'listing.inquiries',
                'indLotLeaseRates',
                'indLotTurnoverConditions',
                'indLotListingPropertyDetails'
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $indlots->items(),
            'meta' => [
                'current_page' => $indlots->currentPage(),
                'per_page' => $indlots->perPage(),
                'total' => $indlots->total(),
                'last_page' => $indlots->lastPage(),
                'next_page_url' => $indlots->nextPageUrl(),
                'prev_page_url' => $indlots->previousPageUrl()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $indlot = IndLotListing::withTrashed()->with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'IndLotListingPropertyDetails',
            'IndLotTurnoverConditions',
            'IndLotLeaseRates'


        ])->find($id);

        if ($indlot->trashed()) {
            return response()->json([
                'message' => "Industrial Lot Listing with ID {$id} has been deleted."
            ], 410); // 410 Gone is semantically accurate
        }

        if (!$indlot) {
            return response()->json([
                'message' => "Industrial Lot Listing with ID {$id} does not exist."
            ]);
        }

        return response()->json(['data' => $indlot]);
    }

    use HandlesListingCreation;

    public function store(StoreIndLotListingRequest $request): JsonResponse
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

        $indlot = DB::transaction(function () use ($request, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $data = $request->validated();

            $indlot = IndLotListing::create([
                'PEZA_accredited' => $data['PEZA_accredited']
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

            $listing = $this->createListing($data['listing'], $indlot);
            $this->createListingComponents($listing, $data['listing']);

            $indlot->IndLotListingPropertyDetails()->create($data['ind_lot_listing_property_details'] ?? []);
            $indlot->IndLotTurnoverConditions()->create($data['ind_lot_turnover_conditions'] ?? []);
            $indlot->IndLotLeaseRates()->create($data['ind_lot_lease_rates'] ?? []);

            $listingRedirectUrl = route('indlot.show', ['id' => $indlot->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };

            return $indlot;
        });

        $fullIndLot = IndLotListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'IndLotListingPropertyDetails',
            'IndLotTurnoverConditions',
            'IndLotLeaseRates'
        ])->findOrFail($indlot->id);

        return response()->json([
            'message' => 'Industrial Lot listing successfully created.',
            'data' => $fullIndLot,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 201);
    }

    public function update(UpdateIndLotListingRequest $request, $id): JsonResponse
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

        $indlot = IndLotListing::with([
            'listing',
            'IndLotListingPropertyDetails',
            'IndLotTurnoverConditions',
            'IndLotLeaseRates'
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($indlot, $data, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $indlot->update([
                'PEZA_accredited' => $data['PEZA_accredited'] ?? $indlot->PEZA_accredited,
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

            $this->updateListing($indlot->listing, $data['listing'] ?? []);
            $this->updateListingComponents($indlot->listing, $data['listing'] ?? []);

            $indlot->IndLotListingPropertyDetails()->update($data['ind_lot_listing_property_details'] ?? []);
            $indlot->IndLotTurnoverConditions()->update($data['ind_lot_turnover_conditions'] ?? []);
            $indlot->IndLotLeaseRates()->update($data['ind_lot_lease_rates'] ?? []);

            $listingRedirectUrl = route('indlot.show', ['id' => $indlot->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };
        });

        $updated = IndLotListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'IndLotListingPropertyDetails',
            'IndLotTurnoverConditions',
            'IndLotLeaseRates'
        ])->findOrFail($indlot->id);

        return response()->json([
            'message' => 'Industrial Lot listing successfully updated.',
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
        $indlot = IndLotListing::with([
            'listing',
            'indLotLeaseRates',
            'indLotTurnoverConditions',
            'indLotListingPropertyDetails'
        ])->findOrFail($id);

        DB::transaction(function () use ($indlot) {
            $indlot->delete(); // triggers soft deletes via model event
        });

        return response()->json([
            'message' => 'Industrial Lot listing and related data successfully soft deleted.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $indlot = IndLotListing::withTrashed()->with([
            'listing',
            'indLotLeaseRates',
            'indLotTurnoverConditions',
            'indLotListingPropertyDetails',
        ])->findOrFail($id);

        if (!$indlot->trashed()) {
            return response()->json([
                'message' => 'Industrial lot listing is not deleted and cannot be restored.'
            ], 400);
        }

        DB::transaction(function () use ($indlot) {
            $indlot->restoreCascade();
        });

        return response()->json([
            'message' => 'Industrial lot listing and related data successfully restored.'
        ]);
    }


}
