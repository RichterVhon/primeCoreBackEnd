<?php

namespace App\Http\Controllers\ListingRelated;

use App\Models\Contact;
use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\ListingRelated\Listing;

use App\Traits\HandlesListingCreation;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ListingRelated\WarehouseListing;
use App\Http\Requests\StoreWarehouseListingRequest;
use App\Http\Requests\UpdateWarehouseListingRequest;

class WarehouseListingController extends Controller
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

        $query = WarehouseListing::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), WarehouseListing::searchableFields());
        }

        $rawQuery = $request->query();
        $filterable = WarehouseListing::filterableFields();

        $filters = [];

        //dump('Raw query keys:', array_keys($request->query()));


        foreach ($rawQuery as $key => $value) {
            //dump("🔍 Checking raw key: {$key}");

            if (in_array($key, $filterable)) {
                //dump("✅ Direct match found: {$key}");
                $filters[$key] = $value;
                continue;
            }

            // Try to match known relationships
            $matched = false;
            foreach ($filterable as $filterKey) {
                $normalized = str_replace('.', '_', $filterKey);
                //dump("🔄 Comparing {$key} with normalized filterable: {$normalized}");

                if ($normalized === $key) {
                    //dump("🎯 Matched normalized key: {$key} → {$filterKey}");
                    $filters[$filterKey] = $value;
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                //dump("❌ No match for key: {$key}");
            }
        }




        //dump('Incoming filters:', $filters);

        $query->applyFilters($filters);
        //dd($query->toSql(), $query->getBindings());
        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $warehouses = $query
            ->with([
                'listing.account',
                'listing.location',
                'listing.inquiries',
                'listing.contacts',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',

                // Warehouse-specific component classes
                'warehouseListingPropDetails',
                'warehouseTurnoverConditions',
                'warehouseSpecs',
                'warehouseLeaseRate'
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $warehouses->items(),
            'meta' => [
                'current_page' => $warehouses->currentPage(),
                'per_page' => $warehouses->perPage(),
                'total' => $warehouses->total(),
                'last_page' => $warehouses->lastPage(),
                'next_page_url' => $warehouses->nextPageUrl(),
                'prev_page_url' => $warehouses->previousPageUrl()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::user();
        if (($user->role !== AccountRole::Agent) && ($user->role !== AccountRole::Admin)) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }
        $warehouse = WarehouseListing::withTrashed()->with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'warehouseListingPropDetails',
            'warehouseTurnoverConditions',
            'warehouseSpecs',
            'warehouseLeaseRate'
        ])->find($id);

        if ($warehouse->trashed()) {
            return response()->json([
                'message' => "Warehouse Listing with ID {$id} has been deleted."
            ], 410); // 410 Gone is semantically accurate
        }

        if (!$warehouse) {
            return response()->json([
                'message' => "Warehouse Listing with ID {$id} does not exist."
            ]);
        }

        return response()->json(['data' => $warehouse]);
    }


    use HandlesListingCreation;

    public function store(StoreWarehouseListingRequest $request): JsonResponse
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

        $warehouse = DB::transaction(function () use ($request, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            $data = $request->validated();

            // Create warehouse morph target
            $warehouse = WarehouseListing::create([
                'PEZA_accredited' => $data['PEZA_accredited']
            ]);

            // 🧠 Prepare contacts from email array
            $pivotData = [];

            foreach ($data['listing']['contacts'] ?? [] as $entry) {
                if (!empty($entry['email'])) {
                    $contact = Contact::firstOrCreate(
                        ['email_address' => $entry['email']],
                        [] // ⛑ No name passed — avoids mismatched identity
                    );

                    if ($contact->wasRecentlyCreated) {
                        $createdContacts[] = $contact;
                    }

                    $pivotData[$contact->id] = ['company' => $entry['company'] ?? null];
                }
            }

            // Inject into listing for trait method to use
            $data['listing']['contacts'] = [];

            foreach ($pivotData as $contactId => $pivot) {
                $data['listing']['contacts'][] = [
                    'contact_id' => $contactId,
                    'company' => $pivot['company']
                ];
            }

            // Create listing + attach morph
            $listing = $this->createListing($data['listing'], $warehouse);
            $listingRedirectUrl = route('warehouse.show', ['id' => $warehouse->id]);

            // Add nested listing components
            $this->createListingComponents($listing, $data['listing']);

            // Add warehouse-specific components
            $warehouse->warehouseListingPropDetails()->create($data['warehouse_listing_prop_details'] ?? []);
            $warehouse->warehouseTurnoverConditions()->create($data['warehouse_turnover_conditions'] ?? []);
            $warehouse->warehouseSpecs()->create($data['warehouse_specs'] ?? []);
            $warehouse->warehouseLeaseRate()->create($data['warehouse_lease_rates'] ?? []);

            // Determine appropriate contact redirect URL
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.update', ['id' => $contact->id]),
                default => route('contacts.index')
            };

            return $warehouse;
        });

        // Fetch full listing with relationships
        $fullWarehouse = WarehouseListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'warehouseListingPropDetails',
            'warehouseTurnoverConditions',
            'warehouseSpecs',
            'warehouseLeaseRate'
        ])->findOrFail($warehouse->id);

        return response()->json([
            'message' => 'Warehouse listing successfully created.',
            'data' => $fullWarehouse,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address,
                //'edit_url' => route('contacts.update', ['contact' => $c->id])
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 201);
    }

    public function update(UpdateWarehouseListingRequest $request, $id): JsonResponse
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

        $warehouse = WarehouseListing::with([
            'listing',
            'warehouseListingPropDetails',
            'warehouseTurnoverConditions',
            'warehouseSpecs',
            'warehouseLeaseRate'
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($warehouse, $data, &$createdContacts, &$listingRedirectUrl, &$contactRedirectUrl) {
            // 🧱 Update warehouse morph record
            $warehouse->update([
                'PEZA_accredited' => $data['PEZA_accredited'] ?? $warehouse->PEZA_accredited,
            ]);

            $pivotData = [];

            foreach ($data['listing']['contacts'] ?? [] as $entry) {
                if (!empty($entry['email'])) {
                    $contact = Contact::firstOrCreate(
                        ['email_address' => $entry['email']],
                        [] // ⚠️ No name provided — avoids mismatched identity
                    );

                    if ($contact->wasRecentlyCreated) {
                        $createdContacts[] = $contact;
                    }

                    $pivotData[$contact->id] = ['company' => $entry['company'] ?? null];
                }
            }

            // 💡 Transform to pivot-ready array for trait
            $data['listing']['contacts'] = [];

            foreach ($pivotData as $contactId => $pivot) {
                $data['listing']['contacts'][] = [
                    'contact_id' => $contactId,
                    'company' => $pivot['company']
                ];
            }

            // 🧍 Update listing
            $this->updateListing($warehouse->listing, $data['listing'] ?? []);

            // 🔄 Update components
            $this->updateListingComponents($warehouse->listing, $data['listing'] ?? []);
            $warehouse->warehouseListingPropDetails()->update($data['warehouse_listing_prop_details'] ?? []);
            $warehouse->warehouseTurnoverConditions()->update($data['warehouse_turnover_conditions'] ?? []);
            $warehouse->warehouseSpecs()->update($data['warehouse_specs'] ?? []);
            $warehouse->warehouseLeaseRate()->update($data['warehouse_lease_rates'] ?? []);

            // 📎 Listing show URL
            $listingRedirectUrl = route('warehouse.show', ['id' => $warehouse->id]);

            // 📍 Smart contact redirect logic
            $contactRedirectUrl = match (count($createdContacts)) {
                1 => route('contacts.update', ['id' => $contact->id]),
                default => route('contacts.index')
            };
        });

        $updated = WarehouseListing::with([
            'listing.account',
            'listing.location',
            'listing.leaseDocument',
            'listing.leaseTermsAndConditions',
            'listing.otherDetail',
            'listing.contacts',
            'listing.inquiries',
            'warehouseListingPropDetails',
            'warehouseTurnoverConditions',
            'warehouseSpecs',
            'warehouseLeaseRate'
        ])->findOrFail($warehouse->id);

        return response()->json([
            'message' => 'Warehouse listing successfully updated.',
            'data' => $updated,
            'new_contacts' => collect($createdContacts)->map(fn($c) => [
                'email' => $c->email_address,
                //'edit_url' => route('contacts.edit', ['id' => $c->id])
            ]),
            'contact_redirect_url' => $contactRedirectUrl,
            'listing_show_url' => $listingRedirectUrl
        ], 200);
    }


    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        if (($user->role !== AccountRole::Agent) && ($user->role !== AccountRole::Admin)) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }
        $warehouse = WarehouseListing::with([
            'listing',
            'warehouseListingPropDetails',
            'warehouseTurnoverConditions',
            'warehouseSpecs',
            'warehouseLeaseRate'
        ])->findOrFail($id);

        DB::transaction(function () use ($warehouse) {
            $warehouse->delete(); // triggers soft deletes via model event
        });

        return response()->json([
            'message' => 'Warehouse listing and related data successfully soft deleted.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $user = Auth::user();
        if (($user->role !== AccountRole::Agent) && ($user->role !== AccountRole::Admin)) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }
        $warehouse = WarehouseListing::withTrashed()->with([
            'listing',
            'warehouseSpecs',
            'warehouseLeaseRate',
            'warehouseListingPropDetails',
            'warehouseTurnoverConditions'
        ])->findOrFail($id);

        if (!$warehouse->trashed()) {
            return response()->json([
                'message' => 'Warehouse listing is not deleted and cannot be restored.'
            ], 400);
        }


        DB::transaction(function () use ($warehouse) {
            $warehouse->restoreCascade();
        });

        return response()->json([
            'message' => 'Warehouse listing and related data successfully restored.'
        ]);
    }


}

