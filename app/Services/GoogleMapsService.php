<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    protected PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.google_maps.base_uri', 'https://maps.googleapis.com/maps/api'))
            ->timeout(config('services.google_maps.timeout', 5))
            ->connectTimeout(config('services.google_maps.connect_timeout', 2));
    }

    public function searchNearbyServices(?string $serviceType, float $lat, float $long, int $radiusKm = 10): array
    {
        if (empty($this->getApiKey())) {
            return [];
        }

        $mapping = $this->mapAppTypeToGoogleParams($serviceType);
        $params = array_filter([
            'key' => $this->getApiKey(),
            'location' => "{$lat},{$long}",
            'radius' => $radiusKm * 1000,
            'type' => $mapping['type'],
            'keyword' => $mapping['keyword'],
        ]);

        $response = $this->http->get('/place/nearbysearch/json', $params);

        if (! $response->successful()) {
            return [];
        }

        $payload = $response->json();

        if (! in_array($payload['status'] ?? '', ['OK', 'ZERO_RESULTS'], true)) {
            return [];
        }

        return array_map(fn (array $place) => $this->normalizePlace($place), $payload['results'] ?? []);
    }

    public function getDistanceMatrix(float $originLat, float $originLong, array $destinations): array
    {
        if (empty($this->getApiKey()) || empty($destinations)) {
            return [];
        }

        $destinationStrings = array_map(fn (array $destination) => "{$destination['lat']},{$destination['long']}", $destinations);

        $response = $this->http->get('/distancematrix/json', [
            'key' => $this->getApiKey(),
            'origins' => "{$originLat},{$originLong}",
            'destinations' => implode('|', $destinationStrings),
            'units' => 'metric',
        ]);

        if (! $response->successful()) {
            return [];
        }

        $payload = $response->json();

        if (($payload['status'] ?? '') !== 'OK') {
            return [];
        }

        $elements = $payload['rows'][0]['elements'] ?? [];

        return array_map(function (array $element) {
            if (($element['status'] ?? '') !== 'OK') {
                return [
                    'distance_meters' => null,
                    'duration_seconds' => null,
                    'status' => $element['status'] ?? 'UNKNOWN',
                ];
            }

            return [
                'distance_meters' => $element['distance']['value'] ?? null,
                'duration_seconds' => $element['duration']['value'] ?? null,
                'status' => $element['status'],
            ];
        }, $elements);
    }

    protected function normalizePlace(array $place): array
    {
        return [
            'google_place_id' => $place['place_id'] ?? null,
            'name' => $place['name'] ?? null,
            'type' => $this->inferAppType($place),
            'lat' => $place['geometry']['location']['lat'] ?? null,
            'long' => $place['geometry']['location']['lng'] ?? null,
            'address' => $place['vicinity'] ?? $place['formatted_address'] ?? null,
            'rating' => isset($place['rating']) ? (float) $place['rating'] : null,
            'source' => 'google',
        ];
    }

    protected function mapAppTypeToGoogleParams(?string $serviceType): array
    {
        return match ($serviceType) {
            'vet' => ['type' => 'veterinary_care', 'keyword' => null],
            'pharmacy' => ['type' => 'pharmacy', 'keyword' => null],
            'groomer' => ['type' => null, 'keyword' => 'pet groomer'],
            'boarding' => ['type' => null, 'keyword' => 'pet boarding'],
            'dog_park' => ['type' => 'park', 'keyword' => 'dog park'],
            'pet_shop' => ['type' => 'pet_store', 'keyword' => null],
            default => ['type' => null, 'keyword' => 'pet services'],
        };
    }

    protected function inferAppType(array $place): string
    {
        $types = $place['types'] ?? [];
        $name = strtolower($place['name'] ?? '');

        if (in_array('pet_store', $types, true)) {
            return 'pet_shop';
        }

        if (in_array('veterinary_care', $types, true)) {
            return 'vet';
        }

        if (in_array('pharmacy', $types, true)) {
            return 'pharmacy';
        }

        if (str_contains($name, 'groomer') || str_contains($name, 'grooming')) {
            return 'groomer';
        }

        if (str_contains($name, 'boarding') || str_contains($name, 'kennel')) {
            return 'boarding';
        }

        if (str_contains($name, 'dog park') || in_array('park', $types, true)) {
            return 'dog_park';
        }

        return 'pet_shop';
    }

    protected function getApiKey(): ?string
    {
        return config('services.google_maps.key');
    }
}
