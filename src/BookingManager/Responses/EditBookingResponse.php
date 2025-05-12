<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

class EditBookingResponse
{
    /**
     * Represents the response after editing a booking via the API.
     * See API docs: status can be 'open' or 'error'.
     *
     * @param string $id The unique identifier assigned by Booking Manager.
     * @param ?BookingStatus $status The status of the booking after edit.
     * @param string $arrival Arrival date.
     * @param string $departure Departure date.
     * @param ?string $providerIdentifier Provider's unique identifier for the booking.
     * @param ?string $guestFirstName Guest's first name.
     * @param string $guestLastName Guest's last name.
     * @param ?string $guestEmail Email of the guest.
     * @param ?string $address1 Guest's address line 1.
     * @param ?string $address2 Guest's address line 2.
     * @param ?string $city Guest's city.
     * @param ?string $country Guest's country code.
     * @param ?string $guestPhone Phone number of the guest.
     * @param ?int $adults Number of adults.
     * @param ?int $children Number of children.
     * @param ?string $timeArrival Estimated time of arrival.
     * @param ?string $flight Guest's flight number.
     * @param ?string $notes Additional notes for the booking.
     * @param ?string $propertyId ID of the property.
     * @param ?string $propertyName Name of the property.
     * @param ?string $propertyIdentifier Provider's identifier for the property.
     * @param ?string $message Optional message, typically for errors.
     * @param ?float $rateTotal Total rate excluding discounts and taxes.
     * @param ?float $rateFinal Final rate including discounts, excluding taxes.
     * @param ?float $taxTotal Total tax amount.
     * @param ?float $taxOther Other taxes/fees.
     * @param ?float $taxVat VAT amount.
     * @param ?float $rateWithTaxFinal Final rate including all taxes.
     * @param ?float $prepayment Prepayment amount.
     * @param ?float $balanceDue Balance due amount.
     * @param ?float $fee Channel fee.
     * @param ?string $created Booking creation timestamp.
     * @param ?string $modified Booking modification timestamp.
     */
    public function __construct(
        public readonly string $id,
        public readonly ?BookingStatus $status,
        public readonly string $arrival,
        public readonly string $departure,
        public readonly ?string $providerIdentifier = null,
        public readonly ?string $guestFirstName = null,
        public readonly string $guestLastName,
        public readonly ?string $guestEmail = null,
        public readonly ?string $address1 = null,
        public readonly ?string $address2 = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $guestPhone = null,
        public readonly ?int $adults = null,
        public readonly ?int $children = null,
        public readonly ?string $timeArrival = null,
        public readonly ?string $flight = null,
        public readonly ?string $notes = null,
        public readonly ?string $propertyId = null,
        public readonly ?string $propertyName = null,
        public readonly ?string $propertyIdentifier = null,
        public readonly ?string $message = null,
        public readonly ?float $rateTotal = null,
        public readonly ?float $rateFinal = null,
        public readonly ?float $taxTotal = null,
        public readonly ?float $taxOther = null,
        public readonly ?float $taxVat = null,
        public readonly ?float $rateWithTaxFinal = null,
        public readonly ?float $prepayment = null,
        public readonly ?float $balanceDue = null,
        public readonly ?float $fee = null,
        public readonly ?string $created = null,
        public readonly ?string $modified = null
    ) {}

    /**
     * Maps the raw XML response data to an EditBookingResponse object.
     *
     * @param array $rawResponse The raw response data (content of the <booking> tag or error).
     * @return self The mapped EditBookingResponse object.
     * @throws MappingException If essential data is missing or invalid.
     */
    public static function map(array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse;
            $attributes = $sourceData['@attributes'] ?? [];

            $statusString = isset($sourceData['status']) ? strtolower((string)$sourceData['status']) : null;
            $status = $statusString ? BookingStatus::tryFrom($statusString) : null;
            $message = (string) ($sourceData['message'] ?? '');

            if (isset($sourceData['error'])) {
                $status = BookingStatus::ERROR;
                $message = (string) ($sourceData['error']['message'] ?? $sourceData['error'] ?? 'Unknown error processing edit booking.');
            } elseif (!$status && ($attributes['id'] ?? null)) {
                // Per API docs, status is 'open' or 'error'. If not 'error' and not 'open', treat as error.
                $status = BookingStatus::ERROR;
                $message = $message ?: "Booking status `{$statusString}` is not valid for an edited booking.";
            }

            $guestNameParts = $sourceData['name'] ?? [];
            $guestFirstName = (string) ($guestNameParts['@attributes']['first'] ?? '');
            $guestLastName = (string) ($guestNameParts['@attributes']['last'] ?? '');

            $propertyInfo = $sourceData['property'] ?? [];
            $propertyId = (string) ($propertyInfo['@attributes']['id'] ?? ($attributes['property_id'] ?? ''));
            $propertyName = is_string($propertyInfo) ? $propertyInfo : ($propertyInfo['#text'] ?? null);
            $propertyIdentifier = (string)($propertyInfo['@attributes']['identifier'] ?? '');

            $rateInfo = $sourceData['rate'] ?? [];
            $taxInfo = $rateInfo['tax'] ?? [];

            return new self(
                id: (string) ($attributes['id'] ?? ''),
                status: $status,
                arrival: (string) ($attributes['arrival'] ?? ($sourceData['arrival'] ?? '')),
                departure: (string) ($attributes['departure'] ?? ($sourceData['departure'] ?? '')),
                providerIdentifier: (string) ($attributes['provider_identifier'] ?? null),
                guestFirstName: $guestFirstName ?: null,
                guestLastName: $guestLastName,
                guestEmail: (string) ($sourceData['email'] ?? null),
                address1: (string) ($sourceData['address_1'] ?? null),
                address2: (string) ($sourceData['address_2'] ?? null),
                city: (string) ($sourceData['city'] ?? null),
                country: (string) ($sourceData['country'] ?? null),
                guestPhone: (string) ($sourceData['phone'] ?? null),
                adults: isset($sourceData['amount_adults']) ? (int)$sourceData['amount_adults'] : null,
                children: isset($sourceData['amount_childs']) ? (int)$sourceData['amount_childs'] : null,
                timeArrival: (string) ($sourceData['time_arrival'] ?? null),
                flight: (string) ($sourceData['flight'] ?? null),
                notes: (string) ($sourceData['notes'] ?? null),
                propertyId: $propertyId ?: null,
                propertyName: $propertyName,
                propertyIdentifier: $propertyIdentifier ?: null,
                message: $message ?: null,
                rateTotal: isset($rateInfo['total']) ? (float)$rateInfo['total'] : null,
                rateFinal: isset($rateInfo['final']) ? (float)$rateInfo['final'] : null,
                taxTotal: isset($taxInfo['@attributes']['total']) ? (float)$taxInfo['@attributes']['total'] : null,
                taxOther: isset($taxInfo['other']) ? (float)$taxInfo['other'] : null,
                taxVat: isset($taxInfo['vat']) ? (float)$taxInfo['vat'] : null,
                rateWithTaxFinal: isset($taxInfo['final']) ? (float)$taxInfo['final'] : null,
                prepayment: isset($rateInfo['prepayment']) ? (float)$rateInfo['prepayment'] : null,
                balanceDue: isset($rateInfo['balance_due']) ? (float)$rateInfo['balance_due'] : null,
                fee: isset($rateInfo['fee']) ? (float)$rateInfo['fee'] : null,
                created: (string) ($sourceData['created'] ?? null),
                modified: (string) ($sourceData['modified'] ?? null)
            );
        } catch (\Throwable $e) {
            throw new MappingException('Error mapping EditBookingResponse: ' . $e->getMessage(), 0, $e);
        }
    }
}
