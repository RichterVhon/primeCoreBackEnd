<?php

namespace App\Http\Controllers\ListingRelated;

use App\Enums\AccountRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HandlesListingCreation;
use App\Models\ListingRelated\IndLotListing;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreIndLotListingRequest;
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

        if ($request->filled('search')) {
            $query->search($request->input('search'), IndLotListing::searchableFields());
        }

        $query->applyFilters($request->only(IndLotListing::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $indlots = $query
            ->with([
                'listing.account',
                'listing.location',
                'listing.inquiries',
                'listing.contacts',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',

                //IndLot-Specific component classes

                'IndLotListingPropertyDetails',
                'IndLotTurnoverConditions',
                'IndLotLeaseRates'
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
        $indlot = IndLotListing::with([
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

         
        ])->findOrFail($id);

        return response()->json(['data' => $indlot]);
    }    

    use HandlesListingCreation;

    public function store(StoreIndLotListingRequest $request): JsonResponse
    {
        $indlot = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Create warehouse morph target
            $indlot = IndLotListing::create([
                'PEZA_accredited' => $data['PEZA_accredited']
            ]);

            // Create listing + attach morph
            $listing = $this->createListing($data['listing'], $indlot);

            // 📎 Add nested listing components
            $this->createListingComponents($listing, $data['listing']);

            // Add warehouse-specific components
            $indlot->IndLotListingPropertyDetails()->create($data['ind_lot_listing_property_details'] ?? []);
            $indlot->IndLotTurnoverConditions()->create($data['ind_lot_turnover_conditions'] ?? []);
            $indlot->IndLotLeaseRates()->create($data['ind_lot_lease_rates'] ?? []);
            return $indlot;
        });

        // ⏪ Fetch inserted data with full relationships
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
            'message' => 'Industrial Lot listing successfully created with all components.',
            'data' => $fullIndLot
        ], 201);
    }

}
