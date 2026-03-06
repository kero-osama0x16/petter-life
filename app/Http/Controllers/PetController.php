<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;            
use Illuminate\Support\Facades\Auth; 

class PetController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->pets);
    }

    public function create()
    { 
        // show form to create a new pet
        return view('pets.create');
        
    }

    public function show(Request $request,Pet $pet)
    {

        // get the pet by id
        // if API return json response
        // if web return view with pet data
        

        if ($pet->user_id !== Auth::id()) {
            return $request->wantsJson() 
                ? response()->json(['message' => 'Unauthorized'], 403) 
                : abort(403);
        }

        if ($request->wantsJson()) {
            // Load media (photo) if using Spatie
            return response()->json($pet->load('media'));
        }
        return view('pets.show', compact('pet'));
    }

    public function store(Request $request)
    {
        // Logic to create a new pet
        // validate the request data
        // create the pet
        // save it in the database
        // if API return json response with the created pet data
        // if web redirect to the pet index page with success message

        //verfication
        $validated = $request->validate([
            'name' => ['required', 'min:3'],
            'type' => ['required', 'min:3'],
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:dog,cat,bird,rabit,fish,hamster,other'],
            'breed' => ['required', 'min:3'],
            'gender' => ['required','in:male,female'],
            'age' =>['required','numeric','min:0'],
            'birthday' => ['required', 'date'],
            'personality' =>['required', 'min:3'],
            'color' => ['required', 'min:3'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);
         
        //testing version with nullable
        /*
        $validated = $request->validate([
            'name' => ['nullable', 'min:3'],
            'type' => ['nullable', 'in:dog,cat,bird,rabbit,fish,hamster,other'],
            'user_id' => ['required', 'exists:users,id'],
            'breed' => ['nullable'],
            'gender' => ['nullable', 'in:male,female'],
            'age' => ['nullable', 'numeric'],
            'birthday' => ['nullable', 'date'],
            'personality' => ['nullable'],
            'color' => ['nullable'],
        ]);
        */
        //create
        $pet = $request->user()->pets()->create($validated);
        
        if ($request->hasFile('photo')) {
            $pet->addMediaFromRequest('photo')->toMediaCollection('pet_avatars');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Pet created successfully',
                'pet' => $pet->load('media'),
            ], 201);
        }
        
        if($request->wantsJson()) 
        {
            // return json response with the created pet data
            return response()->json([
                'message' => 'Pet created successfully',
                'pet' => $pet,
            ], 201);
        } 
        else 
        {
            // redirect to the pet index page with success message
            return redirect('/pets')->with('success', 'Pet created successfully');
        }
    
    }

    public function edit(Pet $pet)
    {
        //  show form to edit an existing pet
        //  TODO impmnent an authization gate to check if the user 
        //  makign this request can edit the pet
            return view('pets.edit', ['pet' => $pet]);
    
    }

    public function update(Request $request, Pet $pet)
    { 

        //  TODO impmnent an authization gate to check if the user 
        //  makign this request can edit the pet

        $validated = $request->validate([
                'name' => ['required', 'min:3'],
                'type' => ['required', 'min:3'],
                'user_id' => ['required', 'exists:users,id'],
                'type' => ['required', 'in:dog,cat,bird,rabit,fish,hamster,other'],
                'breed' => ['required', 'min:3'],
                'gender' => ['required','in:male,female'],
                'age' =>['required','numeric','min:0'],
                'birthday' => ['required', 'date'],
                'personality' =>['required', 'min:3'],
                'color' => ['required', 'min:3'],
            ]);
        // Logic to update an existing pet
        $pet->update($validated);

        if($request->wantsJson()) 
        {
            // return json response with the created pet data
            return response()->json([
                'message' => 'Pet updated successfully',
                'pet' => $pet,
            ], 201);
        } 
        else 
        {
            // redirect to the pet index page with success message
            return redirect('/pets')->with('success', 'Pet updated successfully');
        }    
    }

    public function destroy(Request $request,Pet $pet)
    {
        //  TODO impmnent an authization gate to check if the user 
        //  makign this request can edit the pet
        $pet->delete();
        if($request->wantsJson()) 
        {
            // return json response with the created pet data
            return response()->json([
                'message' => 'Pet deleted successfully',
            ], 201);
        } 
        else 
        {
            // redirect to the pet index page with success message
            return redirect('/pets')->with('success', 'Pet deleted successfully');
        }
        
        // Logic to delete a pet
        
    }
    
}
