<?php

namespace App\Http\Controllers\ListingRelated;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\ListingRelated\OfficeSpaceListing;

class OfficeSpaceListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = OfficeSpaceListing::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), OfficeSpaceListing::searchableFields());
        }

        $query->applyFilters($request->only(OfficeSpaceListing::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $Offices = $query
            ->with([
                'listing.account',
                'listing.location',
                'listing.inquiries',
                'listing.contacts',
                'listing.leaseDocument',
                'listing.otherDetail',
                'listing.leaseTermsAndConditions',

                'OfficeListingPropertyDetails',
                'OfficeTurnoverConditions',
                'OfficeSpecs',
                // 'OfficeLeaseTermsAndConditionsExtn',
                // 'OfficeOtherDetailExtn',
            ])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $Offices->items(),
            'meta' => [
                'current_page' => $Offices->currentPage(),
                'per_page' => $Offices->perPage(),
                'total' => $Offices->total(),
                'last_page' => $Offices->lastPage(),
                'next_page_url' => $Offices->nextPageUrl(),
                'prev_page_url' => $Offices->previousPageUrl()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $Office = OfficeSpaceListing::with([
            'listing.account',
            'listing.location',
            'listing.contacts',
            'listing.leaseDocument',
            'listing.inquiries',
            'listing.otherDetail',
            'listing.leaseTermsAndConditions',

            'OfficeListingPropertyDetails',
            'OfficeTurnoverConditions',
            'OfficeSpecs',
            // 'OfficeLeaseTermsAndConditionsExtn',
            // 'OfficeOtherDetailExtn',
        ])->findOrFail($id);

        return response()->json(['data' => $Office]);
    }
}
