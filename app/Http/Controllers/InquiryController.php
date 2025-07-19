<?php

namespace App\Http\Controllers;

use IntlChar;
use App\Models\Account;
use App\Models\Inquiry;
use App\Enums\AccountRole;
use App\Enums\InquiryStatus;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use App\Models\ListingRelated\Listing;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Auth; // ✅ This is the missing piece

class InquiryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!in_array($user->role, [AccountRole::Agent, AccountRole::Admin])) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = Inquiry::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), Inquiry::searchableFields());
        }

        $query->applyFilters($request->only(Inquiry::filterableFields()));

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $inquiries = $query
            ->with(['agent', 'client', 'listing'])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $inquiries->items(),
            'meta' => [
                'current_page' => $inquiries->currentPage(),
                'per_page' => $inquiries->perPage(),
                'total' => $inquiries->total(),
                'last_page' => $inquiries->lastPage(),
                'next_page_url' => $inquiries->nextPageUrl(),
                'prev_page_url' => $inquiries->previousPageUrl()
            ]
        ]);
    }


    public function show($id): JsonResponse
    {
        $inquiry = Inquiry::with(['account', 'listing'])->findOrFail($id);

        return response()->json(['data' => $inquiry]);
    }




    // public function update(Request $request, $id): JsonResponse
    // {
    //     $user = Auth::user();
    //     if (!in_array($user->role, [AccountRole::Agent, AccountRole::Admin])) {
    //         return response()->json([
    //             'message' => 'Forbidden: Agents or Admin only'
    //         ], Response::HTTP_FORBIDDEN);
    //     }

    //     $inquiry = Inquiry::findOrFail($id);

    //     $data = $request->validate([
    //         'status' => 'in:pending,responded,archived',
    //         'message' => 'string',
    //         'viewing_schedule' => 'nullable|date',
    //     ]);

    //     DB::transaction(fn() => $inquiry->update($data));

    //     $updated = Inquiry::with(['account', 'listing'])->findOrFail($inquiry->id);

    //     return response()->json([
    //         'message' => 'Inquiry successfully updated.',
    //         'data' => $updated
    //     ], 200);    
    // }

    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!in_array($user->role, [AccountRole::Agent, AccountRole::Admin])) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }

        $inquiry = Inquiry::findOrFail($id);

        $data = $request->validate([
            'status' => 'in:pending,responded,archived',
            'message' => 'string',
            'viewing_schedule' => 'nullable|date',
            'client_email' => 'nullable|email',
            'agent_email' => 'nullable|email',
        ]);

        // 🔍 Resolve client email to account ID
        if (!empty($data['client_email'])) {
            $client = Account::where('email', $data['client_email'])->first();
            if (!$client) {
                return response()->json([
                    'message' => 'Client email does not exist',
                    'url' => url('/register?email=' . urlencode($data['client_email']))
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data['client_id'] = $client->id;
            unset($data['client_email']);
        }

        // 🔍 Resolve agent email to account ID
        if (!empty($data['agent_email'])) {
            $agent = Account::where('email', $data['agent_email'])->first();
            if (!$agent) {
                return response()->json([
                    'message' => 'Agent email does not exist',
                    'url' => url('/register?email=' . urlencode($data['agent_email']))
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data['agent_id'] = $agent->id;
            unset($data['agent_email']);
        }

        DB::transaction(fn() => $inquiry->update($data));

        $updated = Inquiry::with(['agent', 'client', 'listing'])->findOrFail($inquiry->id);

        return response()->json([
            'message' => 'Inquiry successfully updated.',
            'data' => $updated
        ], 200);
    }


    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!in_array($user->role, [AccountRole::Agent, AccountRole::Admin])) {
            return response()->json([
                'message' => 'Forbidden: Agents or Admin only'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'client_email' => 'required|email',
            'listing_id' => 'required|exists:listings,id',
            'status' => ['nullable', new Enum(InquiryStatus::class)],
            'message' => 'required|string',
            'viewing_schedule' => 'nullable|date',
        ]);

        $client = Account::where('email', $data['client_email'])->first();

        if (!$client) {
            return response()->json([
                'message' => 'Email does not exist',
                'url' => url('/register?email=' . urlencode($data['client_email']))
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data['client_id'] = $client->id;
        $data['agent_id'] = $user->id;

        $listing = Listing::with('account')->findOrFail($data['listing_id']);

        unset($data['client_email']);

        $inquiry = DB::transaction(fn() => Inquiry::create($data));

        $fullInquiry = Inquiry::with(['agent', 'client', 'listing'])->findOrFail($inquiry->id);

        return response()->json([
            'message' => 'Inquiry successfully created.',
            'data' => $fullInquiry
        ], 201);
    }
}
