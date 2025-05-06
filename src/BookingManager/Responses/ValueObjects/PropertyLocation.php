<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PropertyLocation
{
    public function __construct(
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly string $address,
        public readonly string $zipcode,
        public readonly string $city,
        public readonly string $country,
        public readonly ?float $cityLatitude,
        public readonly ?float $cityLongitude,
        public readonly string $area
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $gpsCoords = null;
        if ($gps = Arr::get($data, 'gps')) {
            $coords = explode(',', (string) $gps);
            $gpsCoords = [
                'lat' => isset($coords[0]) ? (float) trim($coords[0]) : null,
                'lon' => isset($coords[1]) ? (float) trim($coords[1]) : null,
            ];
        }

        $cityGpsCoords = null;
        $cityData = Arr::get($data, 'city', []);
        $cityAttributes = Arr::get($cityData, '@attributes', []);
        if ($cityGps = Arr::get($cityAttributes, 'gps')) {
            $coords = explode(',', (string) $cityGps);
            $cityGpsCoords = [
                'lat' => isset($coords[0]) ? (float) trim($coords[0]) : null,
                'lon' => isset($coords[1]) ? (float) trim($coords[1]) : null,
            ];
        }

        return new self(
            latitude: $gpsCoords['lat'] ?? null,
            longitude: $gpsCoords['lon'] ?? null,
            address: (string) Arr::get($data, 'address', ''),
            zipcode: (string) Arr::get($data, 'zipcode', ''),
            city: (string) (Arr::get($cityData, '#text') ?? Arr::get($cityData, '') ?? ''), // Handle text or direct value
            country: (string) Arr::get($cityAttributes, 'country', ''),
            cityLatitude: $cityGpsCoords['lat'] ?? null,
            cityLongitude: $cityGpsCoords['lon'] ?? null,
            area: (string) Arr::get($data, 'area', '')
        );
    }
}
