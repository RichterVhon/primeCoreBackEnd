<?php

namespace App\Http\Controllers\ListingRelated;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\ListingRelated\WarehouseListing;

class WarehouseListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
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
}
