<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        // Filter by type (vet, pharmacy) as seen in your PDF
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by minimum rating
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        return response()->json($query->latest()->get());
    }

    public function show(Service $service)
    {
        return response()->json($service);
    }

    /**
     * Logic for "Nearby Services" seen on Page 4 of your PDF.
     * Requires 'lat' and 'long' from the mobile app's GPS.
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'radius' => 'nullable|numeric' // in kilometers
        ]);

        $latitude = $request->lat;
        $longitude = $request->long;
        $radius = $request->radius ?? 10; // Default 10km

        /* This is a simplified distance query using the Haversine formula.
           6371 is the Earth's radius in kilometers.
        */
        $services = Service::selectRaw("*, ( 6371 * acos( cos( radians(?) ) *
            cos( radians( lat ) ) * cos( radians( `long` ) - radians(?) ) +
            sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance", 
            [$latitude, $longitude, $latitude])
            ->having("distance", "<", $radius)
            ->orderBy("distance")
            ->get();

        return response()->json($services);
    }
}