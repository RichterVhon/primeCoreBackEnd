<?php

namespace App\Http\Controllers\ListingRelated;

use App\Enums\AccountRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ListingRelated\RetailOfficeListing;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}
