<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PetController; 
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


// ---------------------------
// PUBLIC API ROUTES
// ---------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// ---------------------------
// PROTECTED API ROUTES
// ---------------------------
Route::middleware('auth:sanctum')->group(function () {
    
    // ---------------------------
    // Auth & Profile
    // ---------------------------
    Route::post('/profile/complete', [AuthController::class, 'completeProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ---------------------------
    // PROTECTED API ROUTES
    // ---------------------------

    // Pets
    Route::controller(PetController::class)->group(function () 
    {
        Route::get('/pets', 'index');
        Route::get('/pets/create', 'create'); 
        Route::post('/pets/store', 'store');
        Route::get('/pets/{pet}', 'show');
        Route::get('/pets/{pet}/edit', 'edit'); 
        Route::patch('/pets/{pet}/update', 'update'); 
        Route::delete('/pets/{pet}/delete', 'destroy'); 
    });
    // medical records
    // reminders
    // services(map stuff)
    
});