<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListingRelated\ListingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ListingRelated\IndLotListingController;
use App\Http\Controllers\ListingRelated\CommLotListingController;
use App\Http\Controllers\ListingRelated\WarehouseListingController;
use App\Http\Controllers\ListingRelated\RetailOfficeListingController;

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

// mga routes na need ng active user session
// Group for all listings
Route::prefix('listings')->middleware(['auth'])->group(function () {
    Route::get('/', [ListingController::class, 'index'])->name('listings.index');;
    Route::get('/{id}', [ListingController::class, 'show'])->name('listings.show');
    Route::post('/warehouse', [WarehouseListingController::class, 'store'])->name('listings.warehouse.store')->middleware('can:create,App\Models\ListingRelated\Listing');
    Route::post('/indlot', [IndLotListingController::class, 'store'])->name('listings.indlot.store')->middleware('can:create,App\Models\ListingRelated\Listing');

});

// Group for warehouse-specific listings
Route::prefix('warehouselistings')->middleware(['auth'])->group(function () {
    Route::get('/', [WarehouseListingController::class, 'index'])->name('warehouse.index');
    Route::get('/{id}', [WarehouseListingController::class, 'show'])->name('warehouse.show');
    Route::post('/', [WarehouseListingController::class, 'store'])->name('warehouse.store');
});

// Group for IndLots=specific listings
Route::prefix('indlotlistings')->middleware(['auth'])->group(function () {
    Route::get('/', [IndLotListingController::class, 'index']);
    Route::get('/{id}', [IndLotListingController::class, 'show']);
    Route::post('/', [IndLotListingController::class, 'store'])->name('indlot.store');

});

//Group for CommLot-specific listings
Route::prefix('commlotlistings')->middleware(['auth'])->group(function () {
    Route::get('/', [CommLotListingController::class, 'index']);
    Route::get('/{id}', [CommLotListingController::class, 'show']);
});

//Group for retail-specific listings
Route::prefix('retailofficelistings')->middleware(['auth'])->group(function () {
    Route::get('/', [RetailOfficeListingController::class, 'index']);
    Route::get('/{id}', [RetailOfficeListingController::class, 'show']);
});


require __DIR__.'/auth.php';
