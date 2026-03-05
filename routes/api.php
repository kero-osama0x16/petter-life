<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





// Public API Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']); // We can wrap this next
Route::post('/reset-password', [AuthController::class, 'resetPassword']); // We can wrap this next

// Protected API Routes (Authenticated Mobile App)
Route::middleware('auth:sanctum')->group(function () {
    // This is where Step 1 of Onboarding happens!
    Route::post('/profile/complete', [AuthController::class, 'completeProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});