<?php

namespace App\Http\Controllers\ListingRelated;

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

        if ($request->filled('search')) {
            $query->search($request->input('search'), CommLotListing::searchableFields());
        }

        $query->applyFilters($request->only(CommLotListing::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $commlots = $query
            ->with([
                'listing.account',
                'listing.location',
                'listing.inquiries',
                'listing.contacts',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',

                //CommLot-Specific component classes

                'CommLotListingPropertyDetails',
                'CommLotTurnoverConditions'
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
        $commlot = CommLotListing::with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'CommLotListingPropertyDetails',
            'CommLotTurnoverConditions'

         
        ])->findOrFail($id);

        return response()->json(['data' => $commlot]);
    }    
    use HandlesListingCreation;

    public function store(StoreCommLotListingRequest $request): JsonResponse
    {
        $commLot = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // 🏗️ Create CommLot morph target
            $commLot = CommLotListing::create([
                //'PEZA_accredited' => $data['PEZA_accredited'] ?? false
            ]);

            // 🔗 Create listing + attach morph
            $listing = $this->createListing($data['listing'], $commLot);

            // 📎 Add nested listing components
            $this->createListingComponents($listing, $data['listing']);

            // 🧱 Add CommLot-specific components
            if (!empty($data['comm_lot_turnover_conditions'])) {
                $commLot->commLotTurnoverConditions()->create($data['comm_lot_turnover_conditions']);
            }

            if (!empty($data['comm_lot_listing_property_details'])) {
                $commLot->commLotListingPropertyDetails()->create($data['comm_lot_listing_property_details']);
            }

            return $commLot;
        });

        // ⏪ Fetch inserted data with full relationships
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
            'message' => 'CommLot listing successfully created with all components.',
            'data' => $fullCommLot
        ], 201);
    }
    
}

