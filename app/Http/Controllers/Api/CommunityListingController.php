<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityListing;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityListingController extends Controller
{
    /**
     * Get all available pets for adoption/breeding (public browse).
     * GET /community/pets
     */
    public function index(Request $request)
    {
        $query = CommunityListing::where('is_active', true)
            ->with(['pet' => function ($q) {
                $q->with('media');
            }, 'user:id,name,city']);

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['adoption', 'breeding'])) {
            $query->where('listing_type', $request->type);
        }

        // Filter by breed
        if ($request->has('breed')) {
            $query->whereHas('pet', function ($q) {
                $q->where('breed', 'like', '%' . request()->breed . '%');
            });
        }

        // Filter by gender
        if ($request->has('gender') && in_array($request->gender, ['male', 'female'])) {
            $query->whereHas('pet', function ($q) {
                $q->where('gender', request()->gender);
            });
        }

        // Filter by pet type
        if ($request->has('pet_type')) {
            $query->whereHas('pet', function ($q) {
                $q->where('type', request()->pet_type);
            });
        }

        $listings = $query->paginate(15);

        return response()->json($listings);
    }

    /**
     * Get a specific pet's listing details (public).
     * GET /community/pets/{petId}
     */
    public function show($petId)
    {
        $listing = CommunityListing::where('is_active', true)
            ->whereHas('pet', fn ($q) => $q->where('id', $petId))
            ->with(['pet' => function ($q) {
                $q->with('media');
            }, 'user:id,name,city'])
            ->first();

        if (!$listing) {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        return response()->json($listing);
    }

    /**
     * Create a listing (adoption or breeding).
     * POST /community/pets/{pet}
     * Body: {type: "adoption|breeding", description: "..."}
     */
    public function store(Request $request, Pet $pet)
    {
        // Check if user owns the pet
        if ($pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:adoption,breeding',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if pet already listed for this type
        $existingListing = CommunityListing::where('pet_id', $pet->id)
            ->where('listing_type', $validated['type'])
            ->where('is_active', true)
            ->first();

        if ($existingListing) {
            return response()->json([
                'message' => "Pet already listed for {$validated['type']}"
            ], 409);
        }

        $listing = CommunityListing::create([
            'pet_id' => $pet->id,
            'user_id' => Auth::id(),
            'listing_type' => $validated['type'],
            'is_active' => true,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => "Pet listed for {$validated['type']} successfully",
            'listing' => $listing->load('pet', 'user')
        ], 201);
    }

    /**
     * Delete/unlist a pet (soft delete by marking inactive).
     * DELETE /community/pets/{pet}?type=adoption|breeding
     */
    public function destroy(Request $request, Pet $pet)
    {
        // Check if user owns the pet
        if ($pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $type = $request->query('type'); // 'adoption', 'breeding', or null for all

        if ($type && !in_array($type, ['adoption', 'breeding'])) {
            return response()->json(['message' => 'Invalid type. Must be adoption or breeding'], 400);
        }

        $query = $pet->communityListings()->where('is_active', true);

        if ($type) {
            $query->where('listing_type', $type);
        }

        $query->update(['is_active' => false]);

        return response()->json(['message' => 'Pet unlisted successfully']);
    }
}
