<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



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

    public static function fromXml(array $data): self
    {
        $gpsCoords = null;
        $gpsValue = $data['gps'] ?? null;
        if ($gpsValue) {
            $coords = explode(',', (string) $gpsValue);
            $gpsCoords = [
                'lat' => isset($coords[0]) ? (float) trim($coords[0]) : null,
                'lon' => isset($coords[1]) ? (float) trim($coords[1]) : null,
            ];
        }

        $cityGpsCoords = null;
        $cityData = $data['city'] ?? [];
        $cityAttributes = is_array($cityData) && isset($cityData['@attributes']) ? $cityData['@attributes'] : [];
        $cityGpsValue = $cityAttributes['gps'] ?? null;

        if ($cityGpsValue) {
            $coords = explode(',', (string) $cityGpsValue);
            $cityGpsCoords = [
                'lat' => isset($coords[0]) ? (float) trim($coords[0]) : null,
                'lon' => isset($coords[1]) ? (float) trim($coords[1]) : null,
            ];
        }

        $cityName = '';
        if (is_array($cityData) && isset($cityData['#text'])) {
            $cityName = (string) $cityData['#text'];
        } elseif (is_string($cityData)) {
            $cityName = (string) $cityData;
        }

        return new self(
            latitude: $gpsCoords['lat'] ?? null,
            longitude: $gpsCoords['lon'] ?? null,
            address: (string) ($data['address'] ?? ''),
            zipcode: (string) ($data['zipcode'] ?? ''),
            city: $cityName,
            country: (string) ($cityAttributes['country'] ?? ''),
            cityLatitude: $cityGpsCoords['lat'] ?? null,
            cityLongitude: $cityGpsCoords['lon'] ?? null,
            area: (string) ($data['area'] ?? '')
        );
    }
}
