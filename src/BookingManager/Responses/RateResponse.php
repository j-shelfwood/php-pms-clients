<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Exception;
use Tightenco\Collect\Support\Collection;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\StayRate;

class RateResponse
{
    /**
     * Represents the RateResponse class and its purpose as a DTO.
     * This class is responsible for holding booking rate information for a specific stay duration,
     * including taxes, prepayment, and balance due, derived from the info.xml endpoint.
     *
     * @param  float  $final_before_taxes  Final price before taxes (derived from StayRate->final)
     * @param  float  $final_after_taxes  Final price after taxes (derived from StayTax->final)
     * @param  float  $tax_vat  VAT tax amount (derived from StayTax->vatAmount)
     * @param  float  $tax_other  Other tax amount (derived from StayTax->otherAmount)
     * @param  float  $tax_total  Total tax amount (derived from StayTax->total)
     * @param  float  $prepayment  Prepayment amount (derived from StayRate->prepayment)
     * @param  float  $balance_due_remaining  Balance due after prepayment (derived from StayRate->balanceDue)
     */
    public function __construct(
        public readonly float $final_before_taxes,
        public readonly float $final_after_taxes,
        public readonly float $tax_vat,
        public readonly float $tax_other, // Renamed from tax_tourist for clarity based on XML ('other')
        public readonly float $tax_total,
        public readonly float $prepayment,
        public readonly float $balance_due_remaining,
        // Optional: Include property details if needed
        public readonly ?int $propertyId = null,
        public readonly ?string $propertyIdentifier = null,
        public readonly ?int $maxPersons = null,
        public readonly ?bool $available = null,
        public readonly ?int $minimalNights = null
    ) {
        //
    }

    /**
     * Creates a new RateResponse instance from a response Collection (info.xml structure).
     *
     * @param  Collection  $response  The response collection containing booking rate information.
     * @return self Returns an instance of RateResponse populated with data from the response.
     */
    public static function map(Collection|array $response): self
    {
        try {
            // Data is nested under \'property\' in the typical info.xml response
            // However, the provided mock \'get-rate-for-stay.xml\' has \'property\' inside \'info\'
            $infoData = $response->get('info') ?? $response; // Adjust based on whether \'info\' wrapper exists
            $propertyData = collect($infoData->get('property'));

            if ($propertyData->isEmpty()) {
                // Removed Log::error
                throw new Exception('Invalid response structure: Missing property data.');
            }

            if (! $propertyData->has('rate')) {
                // Removed Log::error
                throw new Exception('Invalid response structure: Missing rate data.');
            }

            $rateData = collect($propertyData->get('rate'));
            $stayRate = StayRate::fromXml($rateData);

            $propertyAttributes = $propertyData->get('@attributes', []);

            return new self(
                final_before_taxes: $stayRate->final,
                final_after_taxes: $stayRate->tax->final,
                tax_vat: $stayRate->tax->vatAmount,
                tax_other: $stayRate->tax->otherAmount,
                tax_total: $stayRate->tax->total,
                prepayment: $stayRate->prepayment,
                balance_due_remaining: $stayRate->balanceDue,
                // Map property details as well
                propertyId: (int) ($propertyAttributes['id'] ?? null),
                propertyIdentifier: (string) ($propertyAttributes['identifier'] ?? null),
                maxPersons: (int) ($propertyAttributes['max_persons'] ?? null),
                // Availability might be at the \'info\' level or \'property\' level depending on context
                available: (bool) ($propertyAttributes['available'] ?? $infoData->get('@attributes')['available'] ?? null),
                minimalNights: (int) ($propertyAttributes['minimal_nights'] ?? null)
            );
        } catch (Exception $e) {
            // Re-throw or handle as appropriate for your application flow
            throw new Exception('Failed to map RateResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
