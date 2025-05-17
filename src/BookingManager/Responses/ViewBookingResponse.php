<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Throwable; // Import Throwable

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
     * @param int $adults Number of adults.
     * @param int $children Number of children.
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
        public readonly int $adults,
        public readonly int $children,
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

            $getStringOrNull = function ($value): ?string {
                if ($value === null) return null;
                $val = is_array($value) ? current($value) : $value;
                return ($val === false || $val === null) ? null : (string)$val;
            };

            $getRequiredString = function ($value, string $default = ''): string {
                if ($value === null) return $default;
                $val = is_array($value) ? current($value) : $value;
                return ($val === false || $val === null) ? $default : (string)$val;
            };

            $bookingIdVal = $attributes['id'] ?? null;
            $bookingId = $getRequiredString($bookingIdVal, '');


            if ($bookingId === '') {
                throw new MappingException("Booking ID is missing or invalid in the response.");
            }

            $guestNameParts = [];
            $nameAttributes = $sourceData['name']['@attributes'] ?? $sourceData['name'] ?? [];
            $firstName = $getStringOrNull($nameAttributes['first'] ?? null);
            $lastName = $getStringOrNull($nameAttributes['last'] ?? null);

            if ($firstName) {
                $guestNameParts[] = $firstName;
            }
            if ($lastName) {
                $guestNameParts[] = $lastName;
            }
            $guestName = implode(' ', $guestNameParts);
            if (empty($guestName) && !empty($sourceData['name']) && is_string($sourceData['name'])) {
                 $guestName = (string)$sourceData['name'];
            }
            $guestName = $getRequiredString($guestName, '');


            $statusValue = $sourceData['status'] ?? null;
            $status = $statusValue ? BookingStatus::tryFrom($getRequiredString($statusValue)) : null;

            $rateDetails = $sourceData['rate'] ?? [];
            $taxDetails = $rateDetails['tax'] ?? [];
            $taxAttributes = $taxDetails['@attributes'] ?? [];
            $taxFinalValue = $taxDetails['final'] ?? null;

            $propertyName = null;
            $propertyId = null;
            $propertyIdentifier = null;

            if (isset($sourceData['property'])) {
                $propertyData = $sourceData['property'];
                if (is_string($propertyData)) {
                    $propertyName = $propertyData;
                } elseif (is_array($propertyData)) {
                    $propertyName = $getStringOrNull($propertyData['#text'] ?? ($propertyData['#'] ?? null));
                    $propertyId = $getStringOrNull($propertyData['@attributes']['id'] ?? null);
                    $propertyIdentifier = $getStringOrNull($propertyData['@attributes']['identifier'] ?? null);
                }
            }
            // Ensure required propertyId is set, even if from attributes directly if not in complex property node
            $propertyId = $getRequiredString($propertyId ?: ($attributes['propertyid'] ?? null));


            return new self(
                id: $bookingId,
                status: $status,
                arrival: $getRequiredString($attributes['arrival'] ?? null),
                departure: $getRequiredString($attributes['departure'] ?? null),
                totalPrice: $getRequiredString($attributes['totalprice'] ?? null),
                currency: $getRequiredString($attributes['currency'] ?? null),
                guestName: $guestName,
                guestEmail: $getRequiredString($sourceData['email'] ?? null),
                guestPhone: $getRequiredString($sourceData['phone'] ?? null),
                adults: isset($sourceData['adults']) ? (int)$sourceData['adults'] : 0,
                children: isset($sourceData['children']) ? (int)$sourceData['children'] : 0,
                notes: $getRequiredString($sourceData['notes'] ?? null),
                propertyId: $propertyId,
                roomId: $getRequiredString($attributes['roomid'] ?? null), // Assuming roomid might be an attribute
                rateId: $getRequiredString($attributes['rateid'] ?? null), // Assuming rateid might be an attribute
                providerIdentifier: $getStringOrNull($attributes['provider_identifier'] ?? null),
                channelIdentifier: $getStringOrNull($attributes['channel_identifier'] ?? null),
                address1: $getStringOrNull($sourceData['address_1'] ?? null),
                address2: $getStringOrNull($sourceData['address_2'] ?? null),
                city: $getStringOrNull($sourceData['city'] ?? null),
                country: $getStringOrNull($sourceData['country'] ?? null),
                timeArrival: $getStringOrNull($sourceData['time_arrival'] ?? null),
                flight: $getStringOrNull($sourceData['flight'] ?? null),
                propertyName: $propertyName,
                propertyIdentifier: $propertyIdentifier,
                rateTotal: isset($rateDetails['total']) ? (float)$rateDetails['total'] : null,
                rateFinal: isset($rateDetails['final']) ? (float)$rateDetails['final'] : null,
                taxTotal: isset($taxAttributes['total']) ? (float)$taxAttributes['total'] : null,
                taxOther: isset($taxDetails['other']) ? (float)$taxDetails['other'] : null,
                taxVat: isset($taxDetails['vat']) ? (float)$taxDetails['vat'] : null,
                rateWithTaxFinal: $taxFinalValue !== null ? (float)$taxFinalValue : null,
                prepayment: isset($rateDetails['prepayment']) ? (float)$rateDetails['prepayment'] : null,
                balanceDue: isset($rateDetails['balance_due']) ? (float)$rateDetails['balance_due'] : null,
                fee: isset($rateDetails['fee']) ? (float)$rateDetails['fee'] : null,
                created: $getStringOrNull($sourceData['created'] ?? null),
                modified: $getStringOrNull($sourceData['modified'] ?? null)
            );
        } catch (Throwable $e) { // Use imported Throwable
            // error_log("Mapping error in ViewBookingResponse: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            throw new MappingException("Error mapping ViewBookingResponse: " . $e->getMessage(), 0, $e);
        }
    }
}
