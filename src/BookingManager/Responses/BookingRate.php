<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

/**
 * Read-only DTO representing a booking rate for a stay, as returned by the API.
 */
class BookingRate
{
    public function __construct(
        public readonly float $final_before_taxes,
        public readonly float $final_after_taxes,
        public readonly float $tax_vat,
        public readonly float $tax_tourist,
        public readonly float $tax_total,
        public readonly float $prepayment,
        public readonly float $balance_due_remaining
    ) {}
}
