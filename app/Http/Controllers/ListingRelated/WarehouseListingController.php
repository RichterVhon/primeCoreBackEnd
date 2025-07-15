<?php

namespace App\Http\Controllers\ListingRelated;

use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ListingRelated\Listing;

use Symfony\Component\HttpFoundation\Response;

use App\Models\ListingRelated\WarehouseListing;
use App\Http\Requests\StoreWarehouseListingRequest;

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

        $query->applyFilters($request->only(WarehouseListing::filterableFields()));

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
        $warehouse = WarehouseListing::with([
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
        ])->findOrFail($id);

        return response()->json(['data' => $warehouse]);
    }


    public function store(StoreWarehouseListingRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            
            // Step 1: Morph target — create WarehouseListing
            $warehouse = WarehouseListing::create([
                'peza_accredited' => $data['peza_accredited']
            ]);

            // Step 2: Create Listing + associate morph
            $listingData = $data['listing'];
            $user = Auth::user();
            $listingData['account_id'] = $user->id;

            $listingData['date_uploaded'] = now();
            $listingData['date_last_updated'] = now();
            
            $listing = new Listing($listingData);
            $listing->listable()->associate($warehouse);
            $listing->save();

            // Step 3: Create Listing Components
            $listing->location()->create($data['listing']['location'] ?? []);
            $listing->leaseDocument()->create($data['listing']['lease_document'] ?? []);
            $listing->leaseTermsAndConditions()->create($data['listing']['lease_terms_and_conditions'] ?? []);
            $listing->otherDetail()->create($data['listing']['other_detail'] ?? []);

            // Step 4: Attach Contacts via pivot
            foreach ($data['listing']['contacts'] ?? [] as $contact) {
                $listing->contacts()->attach($contact['contact_id'], [
                    'company' => $contact['company'] ?? null,
                ]);
            }

            // Step 5: Create Warehouse Components
            $warehouse->warehouseListingPropDetails()->create($data['warehouse_listing_prop_details'] ?? []);
            $warehouse->warehouseTurnoverConditions()->create($data['warehouse_turnover_conditions'] ?? []);
            $warehouse->warehouseSpecs()->create($data['warehouse_specs'] ?? []);
            $warehouse->warehouseLeaseRate()->create($data['warehouse_lease_rates'] ?? []);
        });

        return response()->json(['message' => 'Warehouse listing successfully created with all components'], 201);
    }


}

