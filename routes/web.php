<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListingRelated\ListingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ListingRelated\WarehouseListingController;

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

// mga routes na need ng active user session
// Group for all listings
Route::prefix('listings')->middleware(['auth'])->group(function () {
    Route::get('/', [ListingController::class, 'index'])->name('listings.index');;
    Route::get('/{id}', [ListingController::class, 'show'])->name('listings.show');
    Route::post('/warehouse', [WarehouseListingController::class, 'store'])->name('listings.warehouse.store')->middleware('can:create,App\Models\ListingRelated\Listing');
});

// Group for warehouse-specific listings
Route::prefix('warehouselistings')->middleware(['auth'])->group(function () {
    Route::get('/', [WarehouseListingController::class, 'index'])->name('warehouse.index');
    Route::get('/{id}', [WarehouseListingController::class, 'show'])->name('warehouse.show');
    Route::post('/', [WarehouseListingController::class, 'store'])->name('warehouse.store');
});


require __DIR__.'/auth.php';
