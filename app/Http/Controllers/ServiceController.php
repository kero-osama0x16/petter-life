<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\GoogleMapsService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected GoogleMapsService $googleMapsService;

    public function __construct(GoogleMapsService $googleMapsService)
    {
        $this->googleMapsService = $googleMapsService;
    }

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
     * Nearby services for the map feature.
     * Combines local DB service records with live Google Maps Places + Distance Matrix results.
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'radius' => 'nullable|numeric',
            'type' => 'nullable|string',
        ]);

        $latitude = $request->lat;
        $longitude = $request->long;
        $radius = $request->radius ?? 10; // Default 10km

        $query = Service::selectRaw("*, ( 6371 * acos( cos( radians(?) ) *
            cos( radians( lat ) ) * cos( radians( `long` ) - radians(?) ) +
            sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance",
            [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $localServices = $query->get();

        $localPayload = $localServices->map(function (Service $service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'type' => $service->type,
                'lat' => $service->lat,
                'long' => $service->long,
                'address' => $service->address,
                'rating' => $service->rating,
                'distance_meters' => isset($service->distance) ? (float) ($service->distance * 1000) : null,
                'source' => 'local',
            ];
        })->all();

        $googleServices = $this->googleMapsService->searchNearbyServices(
            $request->type,
            $latitude,
            $longitude,
            $radius
        );

        $distanceMatrix = $this->googleMapsService->getDistanceMatrix(
            $latitude,
            $longitude,
            array_map(fn ($item) => [
                'lat' => $item['lat'],
                'long' => $item['long'],
            ], $googleServices)
        );

        $googlePayload = array_map(function (array $item, int $index) use ($distanceMatrix) {
            $distanceInfo = $distanceMatrix[$index] ?? ['distance_meters' => null, 'duration_seconds' => null];

            return [
                'google_place_id' => $item['google_place_id'] ?? null,
                'name' => $item['name'],
                'type' => $item['type'],
                'lat' => $item['lat'],
                'long' => $item['long'],
                'address' => $item['address'],
                'rating' => $item['rating'],
                'distance_meters' => $distanceInfo['distance_meters'] ?? null,
                'duration_seconds' => $distanceInfo['duration_seconds'] ?? null,
                'source' => 'google',
            ];
        }, $googleServices, array_keys($googleServices));

        $combined = array_merge($localPayload, $googlePayload);

        usort($combined, function (array $a, array $b) {
            $distanceA = $a['distance_meters'] ?? INF;
            $distanceB = $b['distance_meters'] ?? INF;
            return $distanceA <=> $distanceB;
        });

        return response()->json($combined);
    }
}