<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

// Example protected route for listings:
// Route::middleware(['auth'])->group(function () {
//     Route::get('/listings', [ListingController::class, 'index']);
// });

require __DIR__.'/auth.php';
