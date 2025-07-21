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
use App\Models\ListingRelated\IndLotListing;
use App\Models\ListingRelated\CommLotListing;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreCommLotListingRequest;
use App\Http\Requests\UpdateCommLotListingRequest;

class CommLotListingController extends Controller
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

        $query = CommLotListing::query();

        // 🔍 Search
        if ($request->filled('search')) {
            $query->search($request->input('search'), CommLotListing::searchableFields());
        }

        // 🧠 Filter normalization
        $rawQuery = $request->query();
        $filterable = CommLotListing::filterableFields();
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

        $commlots = $query
            ->with([
                'listing.location',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',
                'listing.contacts',
                'listing.inquiries',
                'commLotTurnoverConditions',
                'commLotListingPropertyDetails'
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $commlots->items(),
            'meta' => [
                'current_page' => $commlots->currentPage(),
                'per_page' => $commlots->perPage(),
                'total' => $commlots->total(),
                'last_page' => $commlots->lastPage(),
                'next_page_url' => $commlots->nextPageUrl(),
                'prev_page_url' => $commlots->previousPageUrl()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $commlot = CommLotListing::withTrashed()->with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'CommLotListingPropertyDetails',
            'CommLotTurnoverConditions'


        ])->find($id);

        if ($commlot->trashed()) {
            return response()->json([
                'message' => "Commercial Listing with ID {$id} has been deleted."
            ], 410); // 410 Gone is semantically accurate
        }

        if (!$commlot) {
            return response()->json([
                'message' => "Commercial Lot Listing with ID {$id} does not exist."
            ]);
        }

        return response()->json(['data' => $commlot]);
    }
    use HandlesListingCreation;

    public function store(StoreCommLotListingRequest $request): JsonResponse
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

        $commLot = DB::transaction(function () use ($request, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $data = $request->validated();

            $commLot = CommLotListing::create();

            // 📧 Contact creation
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

            $data['listing']['contacts'] = collect($pivotData)->map(function ($pivot, $contactId) {
                return ['contact_id' => $contactId, 'company' => $pivot['company']];
            })->values()->toArray();

            $listing = $this->createListing($data['listing'], $commLot);
            $this->createListingComponents($listing, $data['listing']);

            if (!empty($data['comm_lot_turnover_conditions'])) {
                $commLot->commLotTurnoverConditions()->create($data['comm_lot_turnover_conditions']);
            }

            if (!empty($data['comm_lot_listing_property_details'])) {
                $commLot->commLotListingPropertyDetails()->create($data['comm_lot_listing_property_details']);
            }

            $listingRedirectUrl = route('commlot.show', ['id' => $commLot->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };

            return $commLot;
        });

        $fullCommLot = CommLotListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'commLotTurnoverConditions',
            'commLotListingPropertyDetails'
        ])->findOrFail($commLot->id);

        return response()->json([
            'message' => 'CommLot listing successfully created.',
            'data' => $fullCommLot,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 201);
    }

    public function update(UpdateCommLotListingRequest $request, $id): JsonResponse
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

        $commLot = CommLotListing::with([
            'listing',
            'commLotListingPropertyDetails',
            'commLotTurnoverConditions'
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($commLot, $data, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $commLot->update([
                'PEZA_accredited' => $data['PEZA_accredited'] ?? $commLot->PEZA_accredited,
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

            $data['listing']['contacts'] = collect($pivotData)->map(function ($pivot, $contactId) {
                return ['contact_id' => $contactId, 'company' => $pivot['company']];
            })->values()->toArray();

            $this->updateListing($commLot->listing, $data['listing'] ?? []);
            $this->updateListingComponents($commLot->listing, $data['listing'] ?? []);

            $commLot->commLotListingPropertyDetails()->update($data['comm_lot_listing_property_details'] ?? []);
            $commLot->commLotTurnoverConditions()->update($data['comm_lot_turnover_conditions'] ?? []);

            $listingRedirectUrl = route('commlot.show', ['id' => $commLot->id]);
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.edit', ['id' => $createdContacts[0]->id]),
                default => route('contacts.index')
            };
        });

        $updated = CommLotListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'commLotListingPropertyDetails',
            'commLotTurnoverConditions'
        ])->findOrFail($commLot->id);

        return response()->json([
            'message' => 'CommLot listing successfully updated.',
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
        $commlot = CommLotListing::with([
            'listing',
            'commLotTurnoverConditions',
            'commLotListingPropertyDetails'
        ])->findOrFail($id);

        DB::transaction(function () use ($commlot) {
            $commlot->delete(); // triggers soft deletes via model event
        });

        return response()->json([
            'message' => 'Commercial Lot listing and related data successfully soft deleted.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $commlot = CommLotListing::withTrashed()->with([
            'listing',
            'commLotTurnoverConditions',
            'commLotListingPropertyDetails',
        ])->findOrFail($id);

        if (!$commlot->trashed()) {
            return response()->json([
                'message' => 'Commercial lot listing is not deleted and cannot be restored.'
            ], 400);
        }

        DB::transaction(function () use ($commlot) {
            $commlot->restoreCascade();
        });

        return response()->json([
            'message' => 'Commercial lot listing and related data successfully restored.'
        ]);
    }


}




