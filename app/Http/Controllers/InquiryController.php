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

        // 🔒 Restrict to authorized roles
        if (!in_array($user->role, [AccountRole::Agent, AccountRole::Admin, AccountRole::Client])) {
            return response()->json([
                'message' => 'Forbidden: Only agents, clients, or admins can view inquiries.'
            ], Response::HTTP_FORBIDDEN);
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $query = Inquiry::query();

        // 🔍 Optional search
        if ($request->filled('search')) {
            $query->search($request->input('search'), Inquiry::searchableFields());
        }
        $rawQuery = $request->query();
        $filterable = Inquiry::filterableFields();

        $filters = [];

        //dump('Raw query keys:', array_keys($request->query()));


        foreach ($rawQuery as $key => $value) {
            //dump("🔍 Checking raw key: {$key}");

            if (in_array($key, $filterable)) {
                //dump("✅ Direct match found: {$key}");
                $filters[$key] = $value;
                continue;
            }

            // Try to match known relationships
            $matched = false;
            foreach ($filterable as $filterKey) {
                $normalized = str_replace('.', '_', $filterKey);
                //dump("🔄 Comparing {$key} with normalized filterable: {$normalized}");

                if ($normalized === $key) {
                    //dump("🎯 Matched normalized key: {$key} → {$filterKey}");
                    $filters[$filterKey] = $value;
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                //dump("❌ No match for key: {$key}");
            }
        }
        // 🧪 Apply filters
        $query->applyFilters($request->only(Inquiry::filterableFields()));

        // 🔐 Role-based visibility scope
        if ($user->role !== AccountRole::Admin) {
            $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->id)
                    ->orWhere('client_id', $user->id);
            });
        }

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        // 📦 Load related models and paginate
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
        $user = Auth::user();

        $inquiry = Inquiry::with(['agent', 'client', 'listing'])->findOrFail($id);

        // 🔐 Block if user is not admin and not linked to inquiry
        if (
            $user->role !== AccountRole::Admin &&
            $user->id !== $inquiry->agent_id &&
            $user->id !== $inquiry->client_id
        ) {
            return response()->json([
                'message' => 'Forbidden: You are not authorized to view this inquiry.'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['data' => $inquiry]);
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

    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        // 🔒 Only Admins and Agents can delete
        if (!in_array($user->role, [AccountRole::Admin, AccountRole::Agent])) {
            return response()->json([
                'message' => 'Forbidden: Only agents or admins can delete inquiries.'
            ], Response::HTTP_FORBIDDEN);
        }

        $inquiry = Inquiry::findOrFail($id);

        // 🛑 Agents can only delete their own inquiries
        if (
            $user->role === AccountRole::Agent &&
            $inquiry->agent_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Forbidden: You may only delete your own inquiries.'
            ], Response::HTTP_FORBIDDEN);
        }

        $inquiry->delete(); // Soft-delete only

        return response()->json([
            'message' => 'Inquiry has been archived.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $user = Auth::user();

        // 🔒 Only Admins and Agents may restore
        if (!in_array($user->role, [AccountRole::Admin, AccountRole::Agent])) {
            return response()->json([
                'message' => 'Forbidden: Only agents or admins can restore inquiries.'
            ], Response::HTTP_FORBIDDEN);
        }

        $inquiry = Inquiry::withTrashed()->findOrFail($id);

        // 🛑 Agents can restore only their own inquiries
        if (
            $user->role === AccountRole::Agent &&
            $inquiry->agent_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Forbidden: You may only restore your own inquiries.'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$inquiry->trashed()) {
            return response()->json([
                'message' => 'Inquiry is not deleted.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $inquiry->restore();

        return response()->json([
            'message' => 'Inquiry has been restored.',
            'data' => [
                'id' => $inquiry->id,
                'agent_id' => $inquiry->agent_id,
                'client_id' => $inquiry->client_id,
                'listing_id' => $inquiry->listing_id,
                'status' => $inquiry->status,
                'message' => $inquiry->message,
                'viewing_schedule' => $inquiry->viewing_schedule,
            ]
        ]);
    }

}
