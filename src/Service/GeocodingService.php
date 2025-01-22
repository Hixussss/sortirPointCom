<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCityFromCoordinates(float $latitude, float $longitude): ?array
    {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}";

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            if (isset($data['address']['city'])) {
                return [
                    'city' => $data['address']['city'],
                    'postalCode' => $data['address']['postcode'] ?? null,
                ];
            }
            else {
                return [
                    'town' => $data['address']['town'],
                    'postalCode' => $data['address']['postcode'] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
