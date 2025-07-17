<?php

namespace App\Http\Controllers\ListingRelated;

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


    use HandlesListingCreation;

    public function store(StoreWarehouseListingRequest $request): JsonResponse
    {
        $warehouse = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Create warehouse morph target
            $warehouse = WarehouseListing::create([
                'PEZA_accredited' => $data['PEZA_accredited']
            ]);

            // Create listing + attach morph
            $listing = $this->createListing($data['listing'], $warehouse);

            // 📎 Add nested listing components
            $this->createListingComponents($listing, $data['listing']);

            // Add warehouse-specific components
            $warehouse->warehouseListingPropDetails()->create($data['warehouse_listing_prop_details'] ?? []);
            $warehouse->warehouseTurnoverConditions()->create($data['warehouse_turnover_conditions'] ?? []);
            $warehouse->warehouseSpecs()->create($data['warehouse_specs'] ?? []);
            $warehouse->warehouseLeaseRate()->create($data['warehouse_lease_rates'] ?? []);

            return $warehouse;
        });

        // ⏪ Fetch inserted data with full relationships
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
            'message' => 'Warehouse listing successfully created with all components.',
            'data' => $fullWarehouse
        ], 201);
    }


    public function update(UpdateWarehouseListingRequest $request, $id): JsonResponse
    {
        $warehouse = WarehouseListing::with([
            'listing',
            'warehouseListingPropDetails',
            'warehouseTurnoverConditions',
            'warehouseSpecs',
            'warehouseLeaseRate'
        ])->findOrFail($id);

        $data = $request->validated();

        DB::transaction(function () use ($warehouse, $data) {
            // 🧱 Update warehouse morph record
            $warehouse->update([
                'PEZA_accredited' => $data['PEZA_accredited'] ?? $warehouse->PEZA_accredited,
            ]);

            // 🧍 Update shared listing fields
            $this->updateListing($warehouse->listing, $data['listing'] ?? []);

            // 🔄 Update listing components
            $this->updateListingComponents($warehouse->listing, $data['listing'] ?? []);

            // ⚙️ Update warehouse components
            $warehouse->warehouseListingPropDetails()->update($data['warehouse_listing_prop_details'] ?? []);
            $warehouse->warehouseTurnoverConditions()->update($data['warehouse_turnover_conditions'] ?? []);
            $warehouse->warehouseSpecs()->update($data['warehouse_specs'] ?? []);
            $warehouse->warehouseLeaseRate()->update($data['warehouse_lease_rates'] ?? []);
        });

        // 🧾 Return fully refreshed listing with all relationships
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
            'data' => $updated
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
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


}

