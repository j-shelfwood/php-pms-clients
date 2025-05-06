<?php

namespace Domain\Connections\Mews\Dtos;

use Domain\Dtos\BookingRate;

class RateResponseDto
{
    private array $raw;

    private function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    /**
     * Transform Mews rate response into our BookingRate DTO.
     */
    public function toDomain(): BookingRate
    {
        // TODO: map Mews rate JSON structure to BookingRate::fromResponse format
        // Example mapping (adjust based on actual Mews response):
        $mapped = [
            'property' => [
                'rate' => [
                    'final' => $this->raw['finalPrice'] ?? 0,
                    'tax' => [
                        'vat' => ['#text' => $this->raw['taxes']['vat'] ?? 0],
                        'other' => ['#text' => $this->raw['taxes']['other'] ?? 0],
                        'total' => $this->raw['taxes']['total'] ?? 0,
                    ],
                    'prepayment' => $this->raw['prepaymentAmount'] ?? 0,
                    'balance_due' => $this->raw['balanceDue'] ?? 0,
                ],
            ],
        ];

        return BookingRate::fromResponse($mapped);
    }
}
