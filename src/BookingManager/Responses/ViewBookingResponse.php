<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

class ViewBookingResponse
{
    /**
     * Represents the response after viewing a booking via the API.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $identifier,
        public readonly ?string $providerIdentifier,
        public readonly string $arrival,
        public readonly string $departure,
        public readonly ?BookingStatus $status,
        public readonly string $guestFirstName,
        public readonly string $guestLastName,
        public readonly string $guestEmail,
        public readonly string $guestPhone,
        public readonly string $address1,
        public readonly ?string $address2,
        public readonly string $city,
        public readonly string $country,
        public readonly int $amountAdults,
        public readonly int $amountChilds,
        public readonly ?string $timeArrival,
        public readonly ?string $flight,
        public readonly ?string $notes,
        public readonly string $propertyId,
        public readonly string $propertyIdentifier,
        public readonly string $propertyName,
        public readonly float $rateTotal,
        public readonly float $rateFinal,
        public readonly float $taxTotal,
        public readonly float $taxOther,
        public readonly float $taxVat,
        public readonly float $taxFinal,
        public readonly float $fee,
        public readonly float $prepayment,
        public readonly float $balanceDue,
        public readonly string $created,
        public readonly string $modified
    ) {}

    /**
     * Maps the raw XML response data to a ViewBookingResponse object.
     */
    public static function map(array $rawResponse): self
    {
        try {
            $attributes = $rawResponse['@attributes'] ?? [];

            // Extract basic booking information
            $id = (string) ($attributes['id'] ?? '');
            $identifier = (string) ($attributes['identifier'] ?? '');
            $providerIdentifier = isset($attributes['provider_identifier']) ? (string) $attributes['provider_identifier'] : null;
            $arrival = (string) ($attributes['arrival'] ?? '');
            $departure = (string) ($attributes['departure'] ?? '');

            // Extract guest name
            $nameData = $rawResponse['name'] ?? [];
            $nameAttributes = $nameData['@attributes'] ?? $nameData ?? [];
            $guestFirstName = (string) ($nameAttributes['first'] ?? '');
            $guestLastName = (string) ($nameAttributes['last'] ?? '');

            // Extract guest details
            $guestEmail = (string) ($rawResponse['email'] ?? '');
            $guestPhone = (string) ($rawResponse['phone'] ?? '');
            $address1 = (string) ($rawResponse['address_1'] ?? '');
            $address2 = isset($rawResponse['address_2']) && !is_array($rawResponse['address_2']) && !empty($rawResponse['address_2']) ? (string) $rawResponse['address_2'] : null;
            $city = (string) ($rawResponse['city'] ?? '');
            $country = is_array($rawResponse['country'] ?? '') ? '' : (string) ($rawResponse['country'] ?? '');

            // Extract guest count
            $amountAdults = (int) ($rawResponse['amount_adults'] ?? 0);
            $amountChilds = (int) ($rawResponse['amount_childs'] ?? 0);

            // Extract optional fields
            $timeArrival = isset($rawResponse['time_arrival']) && !is_array($rawResponse['time_arrival']) && !empty($rawResponse['time_arrival']) ? (string) $rawResponse['time_arrival'] : null;
            $flight = isset($rawResponse['flight']) && !is_array($rawResponse['flight']) && !empty($rawResponse['flight']) ? (string) $rawResponse['flight'] : null;
            $notes = isset($rawResponse['notes']) && !is_array($rawResponse['notes']) && !empty($rawResponse['notes']) ? (string) $rawResponse['notes'] : null;

            // Extract property information
            $propertyData = $rawResponse['property'] ?? '';
            if (is_string($propertyData)) {
                // Simple string case - property name only
                $propertyName = $propertyData;
                $propertyId = '';
                $propertyIdentifier = '';
            } else {
                // Complex structure case
                $propertyAttributes = $propertyData['@attributes'] ?? [];
                $propertyId = (string) ($propertyAttributes['id'] ?? '');
                $propertyIdentifier = (string) ($propertyAttributes['identifier'] ?? '');
                $propertyName = (string) ($propertyData['#text'] ?? $propertyData ?? '');
            }

            // Extract status
            $statusString = (string) ($rawResponse['status'] ?? '');
            $status = BookingStatus::tryFrom($statusString);

            // Extract rate information
            $rateData = $rawResponse['rate'] ?? [];
            $rateTotal = (float) ($rateData['total'] ?? 0);
            $rateFinal = (float) ($rateData['final'] ?? 0);
            $fee = (float) ($rateData['fee'] ?? 0);
            $prepayment = (float) ($rateData['prepayment'] ?? 0);
            $balanceDue = (float) ($rateData['balance_due'] ?? 0);

            // Extract tax information
            $taxData = $rateData['tax'] ?? [];
            $taxAttributes = $taxData['@attributes'] ?? [];
            $taxTotal = (float) ($taxAttributes['total'] ?? 0);
            $taxOther = (float) ($taxData['other'] ?? 0);
            $taxVat = (float) ($taxData['vat'] ?? 0);
            $taxFinal = (float) ($taxData['final'] ?? 0);

            // Extract timestamps
            $created = (string) ($rawResponse['created'] ?? '');
            $modified = (string) ($rawResponse['modified'] ?? '');

            return new self(
                id: $id,
                identifier: $identifier,
                providerIdentifier: $providerIdentifier,
                arrival: $arrival,
                departure: $departure,
                status: $status,
                guestFirstName: $guestFirstName,
                guestLastName: $guestLastName,
                guestEmail: $guestEmail,
                guestPhone: $guestPhone,
                address1: $address1,
                address2: $address2,
                city: $city,
                country: $country,
                amountAdults: $amountAdults,
                amountChilds: $amountChilds,
                timeArrival: $timeArrival,
                flight: $flight,
                notes: $notes,
                propertyId: $propertyId,
                propertyIdentifier: $propertyIdentifier,
                propertyName: $propertyName,
                rateTotal: $rateTotal,
                rateFinal: $rateFinal,
                taxTotal: $taxTotal,
                taxOther: $taxOther,
                taxVat: $taxVat,
                taxFinal: $taxFinal,
                fee: $fee,
                prepayment: $prepayment,
                balanceDue: $balanceDue,
                created: $created,
                modified: $modified
            );
        } catch (\Throwable $e) {
            throw new MappingException($e->getMessage(), 0, $e);
        }
    }
}
