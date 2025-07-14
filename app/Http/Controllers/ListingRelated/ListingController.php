<?php

namespace App\Http\Controllers\ListingRelated;

use App\Enums\AccountRole;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ListingRelated\Listing;

use App\Models\ListingRelated\WarehouseListing;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Models\ListingRelated\CommLotListing;
use App\Models\ListingRelated\IndLotListing;
use App\Models\ListingRelated\IndLotListingPropertyDetails;
use App\Models\ListingRelated\RetailOfficeListing;


class ListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = Listing::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), Listing::searchableFields());
        }

        $query->applyFilters($request->only(Listing::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $listings = $query
            ->with(['account', 'listable', 'inquiries'])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $listings->items(),
            'meta' => [
                'current_page' => $listings->currentPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
                'last_page' => $listings->lastPage(),
                'next_page_url' => $listings->nextPageUrl(),
                'prev_page_url' => $listings->previousPageUrl()
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

        $listing = Listing::with([
            'account',
            'location',
            'leaseDocument',
            'otherDetail',
            'leaseTermsAndConditions',
            'contacts',
            'inquiries',
            'listable',
        ])->findOrFail($id);


        /* alternative way
        switch (get_class($listable)) {
            case Warehouse::class:
                $listable->load('specifications', 'insurance');
                break;
            case Apartment::class:
                $listable->load('amenities', 'buildingRules');
                break;
            case JobPost::class:
                $listable->load('requirements', 'benefits');
                break;
        }
        */

        $listable = $listing->listable;
        if ($listable instanceof WarehouseListing) {
            $listable->load('warehouseListingPropDetails', 'warehouseTurnoverConditions', 'warehouseSpecs', 'warehouseLeaseRate');
        } elseif ($listable instanceof OfficeSpaceListing) {
            $listable->load('officeLeaseTermsAndConditionsExtn', 'officeTurnoverConditions', 'officeSpecs', 'OfficeOtherDetailExtn', 'OfficeListingPropertyDetails');
        } elseif ($listable instanceof CommLotListing) {
            $listable->load('commLotTurnoverConditions', 'commLotListingPropertyDetails');
        } elseif ($listable instanceof IndLotListing) {
            $listable->load('indLotLeaseRates', 'indLotTurnoverConditions', 'indLotListingPropertyDetails', );
        } elseif ($listable instanceof RetailOfficeListing) {
            $listable->load('retailOfficeTurnoverConditions', 'retailOfficeListingPropertyDetails', 'retailOfficeBuildingSpecs', '');
        }


        return response()->json(['data' => $listing]);
    }
}
