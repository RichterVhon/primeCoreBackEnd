<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['created_at', 'contact_person', 'email_address', 'position'];
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Validate sort field
        $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'created_at';

        $query = Contact::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'), Contact::searchableFields());
        }

        if (!empty(Contact::filterableFields())) {
            $query->applyFilters($request->only(Contact::filterableFields()));
        }

        $query->orderByRaw("ISNULL($sortField), $sortField $sortDirection");

        $contacts = $query
            ->with(['accounts'])
            ->paginate(10)
            ->appends($request->query());

        return response()->json([
            'data' => $contacts->items(),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
                'last_page' => $contacts->lastPage(),
                'next_page_url' => $contacts->nextPageUrl(),
                'prev_page_url' => $contacts->previousPageUrl()
            ]
        ]);
    }


    public function show($id): JsonResponse
    {
        $contact = Contact::with(['accounts'])->findOrFail($id);

        return response()->json(['data' => $contact]);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = DB::transaction(function () use ($request) {
            $data = $request->validated();

            // new contact
            $contact = Contact::create([
                'contact_person' => $data['contact_person'],
                'position' => $data['position'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'email_address' => $data['email_address'] ?? null,
            ]);

            // pivoting to acc
            if (!empty($data['accounts'])) {
                $pivotPayload = collect($data['accounts'])->mapWithKeys(fn($entry) => [
                    $entry['account_id'] => ['company_name' => $entry['company_name'] ?? null]
                ]);

                $contact->accounts()->syncWithoutDetaching($pivotPayload);
            }

            return $contact->load([
                'accounts' => fn($q) => $q->withPivot('company_name')
            ]);
        });

        return response()->json([
            'message' => 'Contact successfully created and linked to accounts.',
            'data' => $contact
        ], 201);
    }


    // public function update(UpdateContactRequest $request, $id): JsonResponse
    // {
    //     $contact = Contact::findOrFail($id);
    //     $contact->update($request->validated());

    //     return response()->json([
    //         'message' => 'Contact successfully updated.',
    //         'data' => $contact
    //     ]);
    // }

    // public function destroy($id): JsonResponse
    // {
    //     Contact::findOrFail($id)->delete();

    //     return response()->json(['message' => 'Contact successfully deleted.']);
    // }
}
