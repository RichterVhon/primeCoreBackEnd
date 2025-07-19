<?php

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InquiryController;
use App\Models\ListingRelated\OfficeSpaceListing;
use App\Http\Controllers\AccountContactController;
use App\Http\Controllers\Auth\RegisteredAccountController;
use App\Http\Controllers\ListingRelated\ListingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ListingRelated\IndLotListingController;
use App\Http\Controllers\ListingRelated\CommLotListingController;
use App\Http\Controllers\ListingRelated\WarehouseListingController;
use App\Http\Controllers\ListingRelated\OfficeSpaceListingController;
use App\Http\Controllers\ListingRelated\RetailOfficeListingController;


Route::post('/register', [RegisteredAccountController::class, 'store'])->name('auth.register');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

// mga routes na need ng active user session
// Group for all listings
Route::prefix('listings')->middleware(['auth'])->group(function () {
    Route::get('/', [ListingController::class, 'index'])->name('listings.index');
    ;
    Route::get('/{id}', [ListingController::class, 'show'])->name('listings.show');
    Route::delete('/{id}', [ListingController::class, 'destroy'])->name('listings.destroy');

    Route::post('/warehouse', [WarehouseListingController::class, 'store'])->name('listings.warehouse.store');
    Route::post('/officespace', [OfficeSpaceListingController::class, 'store'])->name('listings.officespace.store');
    Route::post('/indlot', [IndLotListingController::class, 'store'])->name('listings.indlot.store');
    Route::post('/commlot', [CommLotListingController::class, 'store'])->name('listings.commlot.store');
    Route::post('/retailoffice', [RetailOfficeListingController::class, 'store'])->name('listings.retailoffice.store');

    Route::put('/officespace/{id}', [OfficeSpaceListingController::class, 'update'])->name('listings.officespace.update');
    Route::put('/warehouse/{id}', [WarehouseListingController::class, 'update'])->name('listings.warehouse.update');
    Route::put('/retailoffice/{id}', [RetailOfficeListingController::class, 'update'])->name('listings.retailofficespace.update');
    Route::put('/indlot/{id}', [IndLotListingController::class, 'update'])->name('listings.indlot.update');
    Route::put('/commlot/{id}', [CommLotListingController::class, 'update'])->name('listings.commlot.update');

    Route::post('{id}/restore', [ListingController::class, 'restore'])->name('listings.restore');


});

// Group for warehouse-specific listings
Route::prefix('warehouselistings')->middleware(['auth'])->group(function () {
    Route::get('/', [WarehouseListingController::class, 'index'])->name('warehouse.index');
    Route::get('/{id}', [WarehouseListingController::class, 'show'])->name('warehouse.show');
    Route::post('/', [WarehouseListingController::class, 'store'])->name('warehouse.store');
    Route::put('/{id}', [WarehouseListingController::class, 'update'])->name('warehouse.update');
    Route::delete('/{id}', [WarehouseListingController::class, 'destroy'])->name('warehouse.destroy');
    Route::post('/{id}/restore', [WarehouseListingController::class, 'restore'])->name('warehouse.restore');
});

// Group for IndLots=specific listings
Route::prefix('indlotlistings')->middleware(['auth'])->group(function () {
    Route::get('/', [IndLotListingController::class, 'index']);
    Route::get('/{id}', [IndLotListingController::class, 'show']);
    Route::post('/', [IndLotListingController::class, 'store'])->name('indlot.store');
    Route::delete('/{id}', [IndLotListingController::class, 'destroy'])->name('indlot.destroy');
    Route::post('/{id}/restore', [IndLotListingController::class, 'restore'])->name('indlot.restore');
});

//Group for CommLot-specific listings
Route::prefix('commlotlistings')->middleware(['auth'])->group(function () {
    Route::get('/', [CommLotListingController::class, 'index']);
    Route::get('/{id}', [CommLotListingController::class, 'show']);
    Route::post('/', [CommLotListingController::class, 'store'])->name('commlot.store');
    Route::delete('/{id}', [CommLotListingController::class, 'destroy'])->name('commlot.destroy');
    Route::post('/{id}/restore', [CommLotListingController::class, 'restore'])->name('commlot.restore');
});

//Group for retail-specific listings
Route::prefix('retailofficelistings')->middleware(['auth'])->group(function () {
    Route::get('/', [RetailOfficeListingController::class, 'index']);
    Route::get('/{id}', [RetailOfficeListingController::class, 'show']);
    Route::post('/', [RetailOfficeListingController::class, 'store'])->name('retailoffice.store');
    Route::put('/{id}', [RetailOfficeListingController::class, 'update'])->name('retailoffice.update');
    Route::delete('/{id}', [RetailOfficeListingController::class, 'destroy'])->name('retailoffice.destroy');
    Route::post('/{id}/restore', [RetailOfficeListingController::class, 'restore'])->name('retailoffice.restore');
});

// Group for office-specific listings
Route::prefix('officespacelistings')->middleware(['auth'])->group(function () {
    Route::get('/', [OfficeSpaceListingController::class, 'index']);
    Route::get('/{id}', [OfficeSpaceListingController::class, 'show']);
    Route::post('/', [OfficeSpaceListingController::class, 'store'])->name('officespace.store');
    Route::put('/{id}', [OfficeSpaceListingController::class, 'update'])->name('officespace.update');
    Route::delete('/{id}', [OfficeSpaceListingController::class, 'destroy'])->name('officespace.destroy');
    Route::post('{id}/restore', [OfficeSpaceListingController::class, 'restore'])->name('officespace.restore');
});

// Account routes
Route::prefix('accounts')->middleware(['auth'])->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::post('/{id}/restore', [AccountController::class, 'restore'])->name('accounts.restore');


    // account to contact routes
    Route::post('/{id}/contacts', [AccountContactController::class, 'store'])->name('accounts.contacts.store');
    Route::get('/{id}/contacts', [AccountContactController::class, 'index'])->name('accounts.contacts.index');
    Route::put('/{account_id}/contacts/{contact_id}', [AccountContactController::class, 'update'])->name('accounts.contacts.update');
    Route::delete('/{account_id}/contacts/{contact_id}', [AccountContactController::class, 'destroy'])->name('accounts.contacts.detach');
});

//for myAccount tab
Route::get('/me', function () {
    $account = Account::with([
        'contacts' => fn($q) => $q->withPivot('company_name'),
        'listings',
        'clientInquiries',
        'agentInquiries',
    ])->findOrFail(Auth::id());

    return response()->json([
        'data' => [
            'id' => $account->id,
            'name' => $account->name,
            'email' => $account->email,
            'role' => $account->role,
            'status' => $account->status,
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at,

            'contacts' => $account->contacts->map(fn($contact) => [
                'id' => $contact->id,
                'contact_person' => $contact->contact_person,
                'email_address' => $contact->email_address,
                'company_name' => optional($contact->pivot)->company_name,
            ])->values(),

            'listings' => $account->listings->map(fn($listing) => [
                'id' => $listing->id,
                'property_name' => $listing->property_name ?? null,
                'date_uploaded' => $listing->date_uploaded,
                'date_last_updated' => $listing->date_last_updated,
                'category' => $listing->listable_type,
                'status' => $listing->status,
            ])->values(),

            'inquiries' => collect()
                ->merge($account->clientInquiries)
                ->merge($account->agentInquiries)
                ->map(fn($inquiry) => [
                    'id' => $inquiry->id,
                    'status' => $inquiry->status,
                    'submitted_at' => $inquiry->created_at,
                ])
                ->unique('id')
                ->values()
        ]
    ]);
})->middleware('auth')->name('accounts.me');

// contact routes
Route::prefix('contacts')->middleware(['auth'])->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/{id}', [ContactController::class, 'show'])->name('contacts.show');
    Route::post('/', [ContactController::class, 'store'])->name('contacts.store');
    Route::put('/{id}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    // Link accounts from the contact side
    Route::get('/{id}/accounts', [AccountContactController::class, 'fromContact'])->name('contacts.accounts.index');
});


//Group for Inquiry
Route::prefix('inquiries')->middleware(['auth'])->group(function () {
    Route::get('/', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/{id}', [InquiryController::class, 'show'])->name('inquiries.show');
    Route::post('/', [InquiryController::class, 'store'])->name('inquiries.store');
    Route::put('/{id}', [InquiryController::class, 'update'])->name('inquiries.update');
});
require __DIR__ . '/auth.php';

