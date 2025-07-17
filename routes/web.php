<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListingRelated\ListingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ListingRelated\IndLotListingController;
use App\Http\Controllers\ListingRelated\CommLotListingController;
use App\Http\Controllers\ListingRelated\WarehouseListingController;
use App\Http\Controllers\ListingRelated\OfficeSpaceListingController;
use App\Http\Controllers\ListingRelated\RetailOfficeListingController;
use App\Models\ListingRelated\OfficeSpaceListing;

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

// mga routes na need ng active user session
// Group for all listings
Route::prefix('listings')->middleware(['auth'])->group(function () {
    Route::get('/', [ListingController::class, 'index'])->name('listings.index');;
    Route::get('/{id}', [ListingController::class, 'show'])->name('listings.show');
    Route::delete('/{id}', [ListingController::class, 'destroy'])->name('listings.destroy');

    Route::post('/warehouse', [WarehouseListingController::class, 'store'])->name('listings.warehouse.store');
    Route::post('/officespace', [OfficeSpaceListingController::class, 'store'])->name('listings.officespace.store');
    Route::post('/indlot', [IndLotListingController::class, 'store'])->name('listings.indlot.store');
    Route::post('/commlot', [CommLotListingController::class, 'store'])->name('listings.commlot.store');
    Route::post('/retailoffice', [RetailOfficeListingController::class,'store'])->name('listings.retailoffice.store');

    Route::put('/warehouse/{id}', [WarehouseListingController::class, 'update'])->name('listings.warehouse.update');
    Route::put('/indlot/{id}', [IndLotListingController::class, 'update'])->name('listings.indlot.update');

});

// Group for warehouse-specific listings
Route::prefix('warehouselistings')->middleware(['auth'])->group(function () {
    Route::get('/', [WarehouseListingController::class, 'index'])->name('warehouse.index');
    Route::get('/{id}', [WarehouseListingController::class, 'show'])->name('warehouse.show');
    Route::post('/', [WarehouseListingController::class, 'store'])->name('warehouse.store');
    Route::delete('/{id}', [WarehouseListingController::class, 'destroy'])->name('warehouse.destroy');
});

// Group for IndLots=specific listings
Route::prefix('indlotlistings')->middleware(['auth'])->group(function () {
    Route::get('/', [IndLotListingController::class, 'index']);
    Route::get('/{id}', [IndLotListingController::class, 'show']);
    Route::post('/', [IndLotListingController::class, 'store'])->name('indlot.store');
    Route::delete('/{id}', [IndLotListingController::class, 'destroy'])->name('indlot.destroy');
});

//Group for CommLot-specific listings
Route::prefix('commlotlistings')->middleware(['auth'])->group(function () {
    Route::get('/', [CommLotListingController::class, 'index']);
    Route::get('/{id}', [CommLotListingController::class, 'show']);
    Route::post('/', [CommLotListingController::class, 'store'])->name('commlot.store');
    Route::delete('/{id}', [CommLotListingController::class, 'destroy'])->name('commlot.destroy');
    
});

//Group for retail-specific listings
Route::prefix('retailofficelistings')->middleware(['auth'])->group(function () {
    Route::get('/', [RetailOfficeListingController::class, 'index']);
    Route::get('/{id}', [RetailOfficeListingController::class, 'show']);
    Route::post('/', [RetailOfficeListingController::class,'store'])->name('retailoffice.store');
    Route::delete('/{id}', [RetailOfficeListingController::class, 'destroy'])->name('retailoffice.destroy');
});

// Group for office-specific listings
Route::prefix('officespacelistings')->middleware(['auth'])->group(function () {
    Route::get('/', [OfficeSpaceListingController::class, 'index']);
    Route::get('/{id}', [OfficeSpaceListingController::class, 'show']);
    Route::post('/', [OfficeSpaceListingController::class, 'store'])->name('officespace.store');
    Route::delete('/{id}', [OfficeSpaceListingController::class, 'destroy'])->name('officespace.destroy');

});



require __DIR__.'/auth.php';
