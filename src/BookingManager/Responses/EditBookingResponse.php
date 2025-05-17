<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Throwable;

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
     * @param string $guestLastName Guest's last name.
     * @param ?string $providerIdentifier Provider's unique identifier for the booking.
     * @param ?string $guestFirstName Guest's first name.
     * @param ?string $guestEmail Email of the guest.
     * @param ?string $address1 Guest's address line 1.
     * @param ?string $address2 Guest's address line 2.
     * @param ?string $city Guest's city.
     * @param ?string $country Guest's country code.
     * @param ?string $guestPhone Phone number of the guest.
     * @param ?int $adults Number of adults.
     * @param ?int $amount_childs Number of children.
     * @param ?string $timeArrival Estimated time of arrival.
     * @param ?string $flight Guest's flight number.
     * @param ?string $notes Additional notes for the booking.
     * @param ?string $propertyId ID of the property.
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
        public readonly string $guestLastName,
        public readonly ?string $providerIdentifier = null,
        public readonly ?string $guestFirstName = null,
        public readonly ?string $guestEmail = null,
        public readonly ?string $address1 = null,
        public readonly ?string $address2 = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $guestPhone = null,
        public readonly ?int $adults = null,
        public readonly ?int $amount_childs = null,
        public readonly ?string $timeArrival = null,
        public readonly ?string $flight = null,
        public readonly ?string $notes = null,
        public readonly ?string $propertyId = null,
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

            if ($status === BookingStatus::ERROR) {
                throw new MappingException("Booking edit failed with error: {$message}");
            }

            $idValue = $attributes['id'] ?? null;
            if (is_array($idValue)) { // Handle cases where XML parser might return an array for a single element
                $idValue = current($idValue);
            }
            $id = (string) $idValue;

            if (empty($id)) {
                throw new MappingException("Booking ID is missing or invalid in the response.");
            }

            $arrivalVal = $attributes['arrival'] ?? null;
            $arrival = $arrivalVal === null ? '' : (is_array($arrivalVal) ? (string)current($arrivalVal) : (string)$arrivalVal);
            $departureVal = $attributes['departure'] ?? null;
            $departure = $departureVal === null ? '' : (is_array($departureVal) ? (string)current($departureVal) : (string)$departureVal);

            if (empty($arrival) || empty($departure)) {
                throw new MappingException("Arrival or Departure date is missing or invalid.");
            }

            $guestLastNameVal = $sourceData['name']['@attributes']['last'] ?? null;
            $guestLastName = $guestLastNameVal === null ? '' : (is_array($guestLastNameVal) ? (string)current($guestLastNameVal) : (string)$guestLastNameVal);
            if (empty($guestLastName)) {
                throw new MappingException("Guest last name is missing.");
            }

            $amountChilds = isset($sourceData['amount_childs']) ? (int)$sourceData['amount_childs'] : null;
            $adults = isset($sourceData['amount_adults']) ? (int)$sourceData['amount_adults'] : null;

            $getStringOrNull = function ($value): ?string {
                if ($value === null) return null;
                $val = is_array($value) ? current($value) : $value;
                return ($val === false || $val === null) ? null : (string)$val;
            };

            $providerIdentifier = $getStringOrNull($attributes['provider_identifier'] ?? null);
            $guestFirstName = $getStringOrNull($sourceData['name']['@attributes']['first'] ?? null);
            $guestEmail = $getStringOrNull($sourceData['email'] ?? null);
            $address1 = $getStringOrNull($sourceData['address_1'] ?? null);
            $address2 = $getStringOrNull($sourceData['address_2'] ?? null);
            $city = $getStringOrNull($sourceData['city'] ?? null);
            $country = $getStringOrNull($sourceData['country'] ?? null);
            $guestPhone = $getStringOrNull($sourceData['phone'] ?? null);
            $timeArrival = $getStringOrNull($sourceData['time_arrival'] ?? null);
            $flight = $getStringOrNull($sourceData['flight'] ?? null);
            $notes = $getStringOrNull($sourceData['notes'] ?? null);
            $propertyId = $getStringOrNull($sourceData['property']['@attributes']['id'] ?? null);
            $propertyName = $getStringOrNull($sourceData['property']['#text'] ?? ($sourceData['property'] ?? null));
            $propertyIdentifier = $getStringOrNull($sourceData['property']['@attributes']['identifier'] ?? null);
            $created = $getStringOrNull($sourceData['created'] ?? null);
            $modified = $getStringOrNull($sourceData['modified'] ?? null);


            // Defensive: never assign amount_childs dynamically after construction
            $response = new self(
                id: $id,
                status: $status,
                arrival: $arrival,
                departure: $departure,
                guestLastName: $guestLastName,
                providerIdentifier: $providerIdentifier,
                guestFirstName: $guestFirstName,
                guestEmail: $guestEmail,
                address1: $address1,
                address2: $address2,
                city: $city,
                country: $country,
                guestPhone: $guestPhone,
                adults: $adults,
                amount_childs: $amountChilds,
                timeArrival: $timeArrival,
                flight: $flight,
                notes: $notes,
                propertyId: $propertyId,
                propertyName: $propertyName,
                propertyIdentifier: $propertyIdentifier,
                rateTotal: isset($sourceData['rate']['total']) ? (float)$sourceData['rate']['total'] : null,
                rateFinal: isset($sourceData['rate']['final']) ? (float)$sourceData['rate']['final'] : null,
                taxTotal: isset($sourceData['rate']['tax']['@attributes']['total']) ? (float)$sourceData['rate']['tax']['@attributes']['total'] : null,
                taxOther: isset($sourceData['rate']['tax']['other']) ? (float)$sourceData['rate']['tax']['other'] : null,
                taxVat: isset($sourceData['rate']['tax']['vat']) ? (float)$sourceData['rate']['tax']['vat'] : null,
                rateWithTaxFinal: isset($sourceData['rate']['tax']['final']) ? (float)$sourceData['rate']['tax']['final'] : null,
                prepayment: isset($sourceData['rate']['prepayment']) ? (float)$sourceData['rate']['prepayment'] : null,
                balanceDue: isset($sourceData['rate']['balance_due']) ? (float)$sourceData['rate']['balance_due'] : null,
                fee: isset($sourceData['rate']['fee']) ? (float)$sourceData['rate']['fee'] : null,
                created: $created,
                modified: $modified
            );

            // No dynamic property assignment allowed
            return $response;
        } catch (MappingException $e) {
            // Re-throw mapping exceptions directly
            throw $e;
        } catch (Throwable $e) {
            // Wrap other exceptions in a MappingException
            throw new MappingException("Error mapping EditBookingResponse: " . $e->getMessage(), 0, $e);
        }
    }
}
