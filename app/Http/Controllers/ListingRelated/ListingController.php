<?php

namespace App\Http\Controllers\ListingRelated;

use App\Enums\AccountRole;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use App\Models\ListingRelated\Listing;
use App\Models\ListingRelated\IndLotListing;
use App\Models\ListingRelated\CommLotListing;
use App\Models\ListingRelated\WarehouseListing;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Models\ListingRelated\RetailOfficeListing;
use App\Models\ListingRelated\IndLotListingPropertyDetails;


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

        // ✅ Use raw keys as passed from frontend
        $filters = array_filter(
            $request->query(),
            fn($key) => in_array($key, Listing::filterableFields()),
            ARRAY_FILTER_USE_KEY
        );

        // // 🐞 Debug: show which filters are being applied
        // foreach ($filters as $key => $value) {
        //     dump("Filter: {$key} = {$value}");
        // }

        $query->applyFilters($filters);
        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        // 🐞 Debug SQL
        //dd($query->toSql(), $query->getBindings());

        $listings = $query
            ->with(['location', 'leaseDocument', 'otherDetail', 'leaseTermsAndConditions', 'contacts', 'listable', 'inquiries'])
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

        $listing = Listing::withTrashed()->with([
            'account',
            'location',
            'leaseDocument',
            'otherDetail',
            'leaseTermsAndConditions',
            'contacts',
            'inquiries',
            'listable',
        ])->find($id);

        if ($listing->trashed()) {
            return response()->json([
                'message' => "Listing with ID {$id} has been deleted."
            ], 410); // 410 Gone is semantically accurate
        }

        if (!$listing) {
            return response()->json([
                'message' => "Listing with ID {$id} does not exist."
            ]);
        }



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

    public function destroy($id): JsonResponse
    {
        $listing = Listing::with([
            'location',
            'leaseDocument',
            'leaseTermsAndConditions',
            'otherDetail',
            'contacts',
            'inquiries',
            'listable'
        ])->findOrFail($id);


        DB::transaction(function () use ($listing) {
            $listing->delete(); // triggers soft deletes via model event
        });

        return response()->json([
            'message' => 'Listing and related data successfully soft deleted.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $listing = Listing::withTrashed()->with([
            'location',
            'leaseDocument',
            'leaseTermsAndConditions',
            'otherDetail',
            'inquiries',
            'contacts',
            'listable'
        ])->findOrFail($id);

        if (!$listing->trashed()) {
            return response()->json([
                'message' => 'Listing is not deleted and cannot be restored.'
            ], 400);
        }

        DB::transaction(function () use ($listing) {
            $listing->restoreCascade(); // assumes Listing model has restoreCascade()
        });

        return response()->json([
            'message' => 'Listing and related data successfully restored.'
        ]);
    }
}
