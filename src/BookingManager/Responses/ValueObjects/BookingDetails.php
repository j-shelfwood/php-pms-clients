<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Carbon\Carbon;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Shelfwood\PhpPms\Exceptions\MappingException;

class BookingDetails
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $identifier,
        public readonly ?string $provider_identifier,
        public readonly ?string $channel_identifier,
        public readonly Carbon $arrival,
        public readonly Carbon $departure,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly ?string $address_1,
        public readonly ?string $address_2,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $phone,
        public readonly int $amount_adults,
        public readonly int $amount_children,
        public readonly ?string $time_arrival,
        public readonly ?string $flight,
        public readonly ?string $notes,
        public readonly int $property_id,
        public readonly ?string $property_identifier,
        public readonly string $property_name,
        public readonly BookingStatus $status,
        public readonly BookingRate $rate,
        public readonly Carbon $created,
        public readonly Carbon $modified
    ) {}

    public static function map(array $data): self
    {
        try {
            $bookingData = $data;
            if (isset($data['booking'])) {
                $bookingData = $data['booking'];
            }

            if (empty($bookingData)) {
                throw new \Exception('Invalid response structure: Missing booking data.');
            }

            $attributes = $bookingData['@attributes'] ?? [];

            // Helper functions for safe data extraction
            $getString = function($key, $default = null) use ($bookingData) {
                $value = $bookingData[$key] ?? $default;
                if (is_array($value) && empty($value)) {
                    return $default;
                }
                if (!is_scalar($value) && $value !== null) {
                    return $default;
                }
                return $value === null ? $default : (string) $value;
            };

            $getInt = function($key, $default = 0) use ($bookingData) {
                $value = $bookingData[$key] ?? $default;
                return is_numeric($value) ? (int) $value : $default;
            };

            $getDate = function($key) use ($bookingData, $attributes) {
                $value = $bookingData[$key] ?? $attributes[$key] ?? null;
                if ($value === null || empty($value) || is_array($value)) {
                    return null;
                }
                try {
                    return Carbon::parse((string) $value);
                } catch (\Exception $e) {
                    return null;
                }
            };

            // Extract name attributes
            $nameData = $bookingData['name'] ?? [];
            $nameAttributes = $nameData['@attributes'] ?? [];

            // Extract property data
            $propertyData = $bookingData['property'] ?? [];
            $propertyAttributes = $propertyData['@attributes'] ?? [];

            return new self(
                id: (int) ($attributes['id'] ?? 0),
                identifier: $attributes['identifier'] ?? null,
                provider_identifier: $attributes['provider_identifier'] ?? null,
                channel_identifier: $attributes['channel_identifier'] ?? null,
                arrival: $getDate('arrival') ?? Carbon::create(1970, 1, 1),
                departure: $getDate('departure') ?? Carbon::create(1970, 1, 1),
                first_name: (string) ($nameAttributes['first'] ?? ''),
                last_name: (string) ($nameAttributes['last'] ?? ''),
                email: $getString('email', ''),
                address_1: $getString('address_1'),
                address_2: $getString('address_2'),
                city: $getString('city'),
                country: $getString('country'),
                phone: $getString('phone'),
                amount_adults: $getInt('amount_adults'),
                amount_children: $getInt('amount_childs'), // Note: API uses 'childs'
                time_arrival: $getString('time_arrival'),
                flight: $getString('flight'),
                notes: $getString('notes'),
                property_id: (int) ($propertyAttributes['id'] ?? 0),
                property_identifier: $propertyAttributes['identifier'] ?? null,
                property_name: is_string($propertyData) ? $propertyData : (string) ($propertyData['name'] ?? $propertyData ?? ''),
                status: BookingStatus::tryFrom($getString('status', 'pending')) ?? BookingStatus::PENDING,
                rate: BookingRate::fromXml($bookingData['rate'] ?? []),
                created: $getDate('created') ?? Carbon::now(),
                modified: $getDate('modified') ?? Carbon::now()
            );
        } catch (\Exception $e) {
            throw new MappingException('Failed to map BookingDetails: ' . $e->getMessage(), 0, $e);
        }
    }
}