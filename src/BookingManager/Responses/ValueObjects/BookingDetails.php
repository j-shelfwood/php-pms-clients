<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Carbon\Carbon;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Shelfwood\PhpPms\BookingManager\Utils\XmlDataExtractor;
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
                arrival: XmlDataExtractor::getDate($bookingData, 'arrival', $attributes) ?? Carbon::create(1970, 1, 1),
                departure: XmlDataExtractor::getDate($bookingData, 'departure', $attributes) ?? Carbon::create(1970, 1, 1),
                first_name: (string) ($nameAttributes['first'] ?? ''),
                last_name: (string) ($nameAttributes['last'] ?? ''),
                email: XmlDataExtractor::getString($bookingData, 'email', ''),
                address_1: XmlDataExtractor::getString($bookingData, 'address_1'),
                address_2: XmlDataExtractor::getString($bookingData, 'address_2'),
                city: XmlDataExtractor::getString($bookingData, 'city'),
                country: XmlDataExtractor::getString($bookingData, 'country'),
                phone: XmlDataExtractor::getString($bookingData, 'phone'),
                amount_adults: XmlDataExtractor::getInt($bookingData, 'amount_adults'),
                amount_children: XmlDataExtractor::getInt($bookingData, 'amount_childs'), // Note: API uses 'childs'
                time_arrival: XmlDataExtractor::getString($bookingData, 'time_arrival'),
                flight: XmlDataExtractor::getString($bookingData, 'flight'),
                notes: XmlDataExtractor::getTextContent($bookingData, 'notes'),
                property_id: (int) ($propertyAttributes['id'] ?? 0),
                property_identifier: $propertyAttributes['identifier'] ?? null,
                property_name: XmlDataExtractor::getTextContent($bookingData, 'property'),
                status: BookingStatus::tryFrom(XmlDataExtractor::getString($bookingData, 'status', 'pending')) ?? BookingStatus::PENDING,
                rate: BookingRate::fromXml($bookingData['rate'] ?? []),
                created: XmlDataExtractor::getDate($bookingData, 'created', $attributes) ?? Carbon::now(),
                modified: XmlDataExtractor::getDate($bookingData, 'modified', $attributes) ?? Carbon::now()
            );
        } catch (\Exception $e) {
            throw new MappingException('Failed to map BookingDetails: ' . $e->getMessage(), 0, $e);
        }
    }
}