<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reminder;
use App\Models\Pet;
use Illuminate\Support\Facades\Auth;

/*
class ReminderController extends Controller
{
    public function index()
    {
        return ["message" => "hello from the index"];
    }

    public function store(Request $request)
    {
        return ["message" => "hello from the store"];
    }

    public function show($record)
    {
        return ["message" => "hello from the show"];
    }

    public function update(Request $request, $record)
    {
        return ["message" => "hello from the update"];
    }

    public function destroy($record)
    {
        return ["message" => "hello from the destroy"];
    }

    public function toggleComplete(Request $request, $record)
    {
        return ["message" => "hello from the toggleComplete"];
    }
}
*/

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        // Get all reminders for pets owned by this user
        // We will order them by date so the newest/upcoming are first
        $reminders = Reminder::whereHas('pet', function ($query) {
            $query->where('user_id', Auth::id());
        })->with('pet:id,name')->orderBy('remind_at', 'asc')->get();

        return response()->json($reminders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'title' => ['required', 'string', 'max:255'],
            'remind_at' => ['required', 'date'], // Can include time like '2024-12-15 14:30:00'
        ]);

        $pet = Pet::find($request->pet_id);
        if ($pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reminder = Reminder::create([
            'pet_id' => $validated['pet_id'],
            'title' => $validated['title'],
            'remind_at' => $validated['remind_at'],
            'is_completed' => false, // Default to false
        ]);

        return response()->json([
            'message' => 'Reminder created successfully',
            'reminder' => $reminder,
        ], 201);
    }

    public function show(Request $request, Reminder $reminder)
    {
        if ($reminder->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($reminder);
    }

    public function update(Request $request, Reminder $reminder)
    {
        if ($reminder->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'remind_at' => ['sometimes', 'required', 'date'],
            'is_completed' => ['sometimes', 'boolean'],
        ]);

        $reminder->update($validated);

        return response()->json([
            'message' => 'Reminder updated successfully',
            'reminder' => $reminder,
        ]);
    }

    public function destroy(Reminder $reminder)
    {
        if ($reminder->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reminder->delete();

        return response()->json(['message' => 'Reminder deleted successfully']);
    }

    // Quick toggle for the UI checkboxes
    public function toggleComplete(Request $request, Reminder $reminder)
    {
        if ($reminder->pet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Flip the current status (true becomes false, false becomes true)
        $reminder->update([
            'is_completed' => !$reminder->is_completed 
        ]);

        return response()->json([
            'message' => 'Reminder status toggled',
            'is_completed' => $reminder->is_completed
        ]);
    }
}
// get an audio file put it in though a speach recongication model and then get the text out of that audio 