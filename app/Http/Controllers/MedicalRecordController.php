<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;



class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {

        // Get all medical records where the associated pet's user_id matches the current user
    $records = MedicalRecord::whereHas('pet', function ($query) 
    {
        $query->where('user_id', Auth::id());
    })->with('media', 'pet:id,name')->get(); // added pet name so you know which pet each record is for

    return response()->json($records);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'type' => ['required', 'in:vaccination,medication,checkup'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            // Strict UI Match: max 10MB (10240 kilobytes)
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], 
        ]);

        // Security Check: Make sure they aren't adding a record to someone else's pet
        $pet = Pet::find($request->pet_id);
        if ($pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Create the record
        $record = MedicalRecord::create([
            'pet_id' => $validated['pet_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
        ]);

        // Handle the File Attachment [cite: 60, 61]
        if ($request->hasFile('attachment')) {
            $record->addMediaFromRequest('attachment')->toMediaCollection('medical_documents');
        }

        return response()->json([
            'message' => 'Medical record created successfully',
            'record' => $record->load('media'),
        ], 201);
    }

    public function show(Request $request, MedicalRecord $record)
    {
        // Security Check: record -> pet -> user_id
        if ($record->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($record->load('media'));
    }

    public function update(Request $request, MedicalRecord $record)
    {
        if ($record->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'required', 'in:vaccination,medication,checkup'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['sometimes', 'required', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $record->update($validated);

        if ($request->hasFile('attachment')) {
            $record->clearMediaCollection('medical_documents'); // Remove old file
            $record->addMediaFromRequest('attachment')->toMediaCollection('medical_documents'); // Add new
        }

        return response()->json([
            'message' => 'Medical record updated successfully',
            'record' => $record->fresh('media'),
        ]);
    }

    public function destroy(MedicalRecord $record)
    {
        if ($record->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $record->delete(); // Spatie will automatically delete the file from storage too!

        return response()->json(['message' => 'Medical record deleted successfully']);
    }
}
