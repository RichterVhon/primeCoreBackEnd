<?php

namespace App\Http\Controllers\ListingRelated;

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

        $query->applyFilters($request->only(RetailOfficeListing::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $retailoffices = $query
            ->with([
                'listing.account',
                'listing.location',
                'listing.inquiries',
                'listing.contacts',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',

                // RetailOffice-specific component classes

                'RetailOfficeListingPropertyDetails',
                'RetailOfficeTurnoverConditions',
                'RetailOfficeBuildingSpecs',
                'RetailOfficeOtherDetailExtn',
    
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $retailoffices->items(),
            'meta' => [
                'current_page' => $retailoffices->currentPage(),
                'per_page' => $retailoffices->perPage(),
                'total' => $retailoffices->total(),
                'last_page' => $retailoffices->lastPage(),
                'next_page_url' => $retailoffices->nextPageUrl(),
                'prev_page_url' => $retailoffices->previousPageUrl()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $retailoffice = RetailOfficeListing::with([
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
        
        ])->findOrFail($id);

        return response()->json(['data' => $retailoffice]);
    }

    use HandlesListingCreation;

    public function store(StoreRetailOfficeListingRequest $request): JsonResponse
    {
        $retailOffice = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Create retail office morph target
            $retailOffice = RetailOfficeListing::create([
                // add any direct retail office fields here if applicable
            ]);

            // Create listing + attach morph
            $listing = $this->createListing($data['listing'], $retailOffice);

            // 📎 Add nested listing components
            $this->createListingComponents($listing, $data['listing']);
            $otherDetail = $listing->otherDetail;

            // 🧱 Create related retail office components
            $retailOffice->retailOfficeListingPropertyDetails()->create($data['retail_office_listing_property_details'] ?? []);
            $retailOffice->retailOfficeTurnoverConditions()->create($data['retail_office_turnover_conditions'] ?? []);
            $retailOffice->retailOfficeBuildingSpecs()->create($data['retail_office_building_specs'] ?? []);
            $retailOffice->retailOfficeOtherDetailExtn()->create(
    array_merge(
                    $data['retail_office_other_detail_extn'] ?? [],
                    ['other_detail_id' => $otherDetail->id]
                )
            );
            return $retailOffice;
        });

        // ⏪ Fetch inserted data with full relationships
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
            'message' => 'Retail office listing successfully created with all components.',
            'data' => $fullRetailOffice
        ], 201);
    }



}
