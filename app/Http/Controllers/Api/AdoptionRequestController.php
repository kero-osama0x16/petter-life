<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdoptionRequest;
use App\Models\ContactExchange;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdoptionRequestController extends Controller
{
    /**
     * Create an adoption/breeding request (protected).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'request_type' => 'required|in:adoption,breeding',
            'message' => 'nullable|string|max:500',
        ]);

        $pet = Pet::findOrFail($validated['pet_id']);
        $requesterId = Auth::id();
        $petOwnerId = $pet->user_id;

        // Prevent self-requests
        if ($requesterId === $petOwnerId) {
            return response()->json(['message' => 'Cannot send request for your own pet'], 409);
        }

        // Check for duplicate pending requests
        $existingRequest = AdoptionRequest::where('pet_id', $pet->id)
            ->where('requester_id', $requesterId)
            ->where('request_type', $validated['request_type'])
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'You already have a pending request for this pet'], 409);
        }

        $adoptionRequest = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'requester_id' => $requesterId,
            'pet_owner_id' => $petOwnerId,
            'request_type' => $validated['request_type'],
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
        ]);

        return response()->json([
            'message' => 'Request sent successfully',
            'request' => $adoptionRequest->load('pet', 'requester', 'petOwner')
        ], 201);
    }

    /**
     * Get user's adoption requests (both sent and received).
     * GET /adoption-requests?filter=sent|received|pending
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $filter = $request->query('filter'); // 'sent', 'received', 'pending'

        if ($filter === 'sent') {
            $requests = AdoptionRequest::where('requester_id', $userId)
                ->with(['pet' => function ($q) {
                    $q->with('media');
                }, 'petOwner:id,name,city', 'contactExchange'])
                ->get();
            return response()->json(['sent' => $requests]);
        } elseif ($filter === 'received') {
            $requests = AdoptionRequest::where('pet_owner_id', $userId)
                ->with(['pet' => function ($q) {
                    $q->with('media');
                }, 'requester:id,name,city', 'contactExchange'])
                ->get();
            return response()->json(['received' => $requests]);
        } elseif ($filter === 'pending') {
            // Get pending requests received by user
            $requests = AdoptionRequest::where('pet_owner_id', $userId)
                ->where('status', 'pending')
                ->with(['pet' => function ($q) {
                    $q->with('media');
                }, 'requester:id,name,city'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return response()->json($requests);
        }

        // Default: return all (both sent and received)
        $sentRequests = AdoptionRequest::where('requester_id', $userId)
            ->with(['pet' => function ($q) {
                $q->with('media');
            }, 'petOwner:id,name,city', 'contactExchange'])
            ->get();

        $receivedRequests = AdoptionRequest::where('pet_owner_id', $userId)
            ->with(['pet' => function ($q) {
                $q->with('media');
            }, 'requester:id,name,city', 'contactExchange'])
            ->get();

        return response()->json([
            'sent' => $sentRequests,
            'received' => $receivedRequests,
        ]);
    }

    /**
     * Update a request (accept/reject).
     * PATCH /adoption-requests/{request}
     * Body: {status: "accepted" | "rejected"}
     */
    public function update(AdoptionRequest $request, Request $validatedRequest)
    {
        // Check if current user is the pet owner
        if ($request->pet_owner_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if request is already responded to
        if ($request->status !== 'pending') {
            return response()->json(['message' => 'Request has already been ' . $request->status], 409);
        }

        $validated = $validatedRequest->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        if ($validated['status'] === 'accepted') {
            $request->accept();
            $message = 'Request accepted. Contact info exchanged.';
        } else {
            $request->reject();
            $message = 'Request rejected.';
        }

        return response()->json([
            'message' => $message,
            'request' => $request->load('pet', 'requester', 'petOwner', 'contactExchange')
        ]);
    }

    /**
     * Get contact info for an accepted request.
     * Requester gets owner's contact, Owner gets requester's contact.
     */
    public function getContact(AdoptionRequest $request)
    {
        $userId = Auth::id();

        // Check if user is either requester or pet owner
        if ($userId !== $request->requester_id && $userId !== $request->pet_owner_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if request is accepted
        if ($request->status !== 'accepted') {
            return response()->json(['message' => 'Contact info only available after request acceptance'], 409);
        }

        $exchange = $request->contactExchange;

        if (!$exchange) {
            return response()->json(['message' => 'No contact exchange found'], 404);
        }

        // Return appropriate contact info based on who's asking
        if ($userId === $request->requester_id) {
            // Requester gets owner's contact
            return response()->json([
                'type' => 'pet_owner_contact',
                'contact' => $exchange->getOwnerContact(),
            ]);
        } else {
            // Owner gets requester's contact
            return response()->json([
                'type' => 'requester_contact',
                'contact' => $exchange->getRequesterContact(),
            ]);
        }
    }

    /**
     * Get a specific adoption request details.
     */
    public function show(AdoptionRequest $request)
    {
        $userId = Auth::id();

        // Check if user is either requester or pet owner
        if ($userId !== $request->requester_id && $userId !== $request->pet_owner_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($request->load('pet', 'requester', 'petOwner', 'contactExchange'));
    }

    /**
     * Delete/cancel a request (only requester can cancel pending requests).
     * DELETE /adoption-requests/{request}
     */
    public function destroy(AdoptionRequest $request)
    {
        // Check if current user is the requester
        if ($request->requester_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if request is still pending
        if ($request->status !== 'pending') {
            return response()->json(['message' => 'Can only cancel pending requests'], 409);
        }

        $request->update(['status' => 'rejected', 'responded_at' => now()]);

        return response()->json(['message' => 'Request cancelled.']);
    }
}
