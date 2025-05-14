<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

class ViewBookingResponse
{
    /**
     * Represents the response after viewing a booking via the API.
     *
     * @param string $id The unique identifier assigned by Booking Manager.
     * @param ?BookingStatus $status The status of the booking.
     * @param string $arrival Arrival date.
     * @param string $departure Departure date.
     * @param string $totalPrice Total price of the booking.
     * @param string $currency Currency of the total price.
     * @param string $guestName Name of the guest.
     * @param string $guestEmail Email of the guest.
     * @param string $guestPhone Phone number of the guest.
     * @param string $adults Number of adults.
     * @param string $children Number of children.
     * @param string $notes Additional notes for the booking.
     * @param string $propertyId ID of the property.
     * @param string $roomId ID of the room (if applicable, often part of propertyId or not specified).
     * @param string $rateId ID of the rate (if applicable).
     * @param ?string $providerIdentifier Provider's unique identifier for the booking.
     * @param ?string $channelIdentifier Channel's unique identifier for the booking.
     * @param ?string $address1 Guest's address line 1.
     * @param ?string $address2 Guest's address line 2.
     * @param ?string $city Guest's city.
     * @param ?string $country Guest's country code.
     * @param ?string $timeArrival Estimated time of arrival.
     * @param ?string $flight Guest's flight number.
     * @param ?string $propertyName Name of the property.
     * @param ?string $propertyIdentifier Provider's identifier for the property.
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
        public readonly string $totalPrice, // Retained for simplicity, though rate breakdown is more detailed
        public readonly string $currency, // Retained for simplicity
        public readonly string $guestName,
        public readonly string $guestEmail,
        public readonly string $guestPhone,
        public readonly string $adults,
        public readonly string $children,
        public readonly string $notes,
        public readonly string $propertyId,
        public readonly string $roomId, // Often, propertyId itself is the unique unit identifier
        public readonly string $rateId, // May not always be present in view response
        public readonly ?string $providerIdentifier = null,
        public readonly ?string $channelIdentifier = null, // Not in create-booking.xml example, but good to have
        public readonly ?string $address1 = null,
        public readonly ?string $address2 = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $timeArrival = null,
        public readonly ?string $flight = null,
        public readonly ?string $propertyName = null,
        public readonly ?string $propertyIdentifier = null,
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
     * Maps the raw XML response data to a ViewBookingResponse object.
     *
     * @param array $rawResponse The raw response data (content of the <booking> tag or error).
     * @return self The mapped ViewBookingResponse object.
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
                $message = (string) ($sourceData['error']['message'] ?? $sourceData['error'] ?? 'Unknown error');
            }

            if (!$status && ($attributes['id'] ?? null)) {
                // If no explicit status but we have an ID, it might imply success or open from other contexts
                // However, for ViewBooking, the API doc says status will be success, open, or error.
                // Defaulting to ERROR if status is not parsable and not an explicit error structure.
                $status = BookingStatus::ERROR;
                $message = $message ?: "Booking status could not be determined from response.";
            }

            $guestNameParts = $sourceData['name'] ?? [];
            $guestName = trim(($guestNameParts['@attributes']['first'] ?? '') . ' ' . ($guestNameParts['@attributes']['last'] ?? ''));

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
                totalPrice: (string) ($rateInfo['total'] ?? ''), // Simplified, use detailed rates below
                currency: (string) ($rateInfo['@attributes']['currency'] ?? ($sourceData['currency'] ?? 'EUR')),
                guestName: $guestName,
                guestEmail: (string) ($sourceData['email'] ?? ''),
                guestPhone: (string) ($sourceData['phone'] ?? ''),
                adults: (string) ($sourceData['amount_adults'] ?? ''),
                children: (string) ($sourceData['amount_childs'] ?? ''),
                notes: (string) ($sourceData['notes'] ?? ''),
                propertyId: $propertyId,
                roomId: (string) ($attributes['room_id'] ?? ''), // Placeholder, adjust if API provides this
                rateId: (string) ($attributes['rate_id'] ?? ''), // Placeholder, adjust if API provides this
                providerIdentifier: (string) ($attributes['provider_identifier'] ?? null),
                channelIdentifier: (string) ($attributes['channel_identifier'] ?? null),
                address1: (string) ($sourceData['address_1'] ?? null),
                address2: (string) ($sourceData['address_2'] ?? null),
                city: (string) ($sourceData['city'] ?? null),
                country: (string) ($sourceData['country'] ?? null),
                timeArrival: (string) ($sourceData['time_arrival'] ?? null),
                flight: (string) ($sourceData['flight'] ?? null),
                propertyName: $propertyName,
                propertyIdentifier: $propertyIdentifier ?: null,
                rateTotal: isset($rateInfo['total']) ? (float)$rateInfo['total'] : null,
                rateFinal: isset($rateInfo['final']) ? (float)$rateInfo['final'] : null,
                taxTotal: isset($taxInfo['@attributes']['total']) ? (float)$taxInfo['@attributes']['total'] : null,
                taxOther: isset($taxInfo['other']) ? (float)$taxInfo['other'] : null,
                taxVat: isset($taxInfo['vat']) ? (float)$taxInfo['vat'] : null,
                rateWithTaxFinal: isset($taxInfo['final']) ? (float)$taxInfo['final'] : null, // This is rate + tax
                prepayment: isset($rateInfo['prepayment']) ? (float)$rateInfo['prepayment'] : null,
                balanceDue: isset($rateInfo['balance_due']) ? (float)$rateInfo['balance_due'] : null,
                fee: isset($rateInfo['fee']) ? (float)$rateInfo['fee'] : null,
                created: (string) ($sourceData['created'] ?? null),
                modified: (string) ($sourceData['modified'] ?? null)
            );
        } catch (\Throwable $e) {
            throw new MappingException('Error mapping ViewBookingResponse: ' . $e->getMessage(), 0, $e);
        }
    }
}
