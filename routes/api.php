<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommunityListingController;
use App\Http\Controllers\Api\AdoptionRequestController;
use App\Http\Controllers\PetController; 
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ServiceController;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


// ---------------------------
// PUBLIC API ROUTES
// ---------------------------

// account management routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

//service routes (for map stuff)
Route::controller(ServiceController::class)->group(function () {
    Route::get('/services', 'index'); 
    Route::get('/services/nearby', 'nearby'); 
    Route::get('/services/{service}', 'show');
});

// Community listings - public browsing
Route::controller(CommunityListingController::class)->group(function () {
    Route::get('/community/pets', 'index');
    Route::get('/community/pets/{petId}', 'show');
});



// ---------------------------
// PROTECTED API ROUTES
// ---------------------------
Route::middleware('auth:sanctum')->group(function () {
    
    // ---------------------------
    // Auth & Profile
    // ---------------------------
    Route::post('/profile/complete', [AuthController::class, 'completeProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/remove-profile', [AuthController::class, 'destroy']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ---------------------------
    // PROTECTED API Controller ROUTES
    // ---------------------------

    // Pets
    Route::controller(PetController::class)->group(function () 
    {
        Route::get('/pets', 'index');
        //Route::get('/pets/create', 'create'); 
        Route::post('/pets/store', 'store');
        Route::get('/pets/{pet}', 'show');
        //Route::get('/pets/{pet}/edit', 'edit'); 
        Route::patch('/pets/{pet}/update', 'update'); 
        Route::delete('/pets/{pet}/delete', 'destroy'); 
    });

    // medical records
    Route::controller(MedicalRecordController::class)->group(function () 
    {
        Route::get('/medical-records', 'index');
        Route::post('/medical-records/store', 'store');
        Route::get('/medical-records/{record}', 'show');
        Route::patch('/medical-records/{record}/update', 'update');
        Route::delete('/medical-records/{record}/delete', 'destroy');
    });

    // reminders
    Route::controller(ReminderController::class)->group(function () 
    {
        Route::get('/reminders', 'index');
        Route::post('/reminders/store', 'store');
        Route::get('/reminders/{reminder}', 'show');
        Route::patch('/reminders/{reminder}/update', 'update');
        Route::delete('/reminders/{reminder}/delete', 'destroy');
        // Bonus route for the UI checkbox
        Route::patch('/reminders/{reminder}/toggle-complete', 'toggleComplete'); 
    });

    // Community listings - protected listing management
    Route::controller(CommunityListingController::class)->group(function () {
        Route::post('/community/pets/{pet}', 'store');
        Route::delete('/community/pets/{pet}', 'destroy');
    });

    // Adoption/Breeding Requests - protected
    Route::controller(AdoptionRequestController::class)->group(function () {
        Route::post('/adoption-requests', 'store');
        Route::get('/adoption-requests', 'index');
        Route::get('/adoption-requests/{request}', 'show');
        Route::patch('/adoption-requests/{request}', 'update');
        Route::delete('/adoption-requests/{request}', 'destroy');
        Route::get('/adoption-requests/{request}/contact', 'getContact');
    });

    // services(map stuff)
    
});