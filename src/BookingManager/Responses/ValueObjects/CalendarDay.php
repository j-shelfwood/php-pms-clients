<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Carbon\Carbon;

class CalendarDay
{
    public function __construct(
        public Carbon $date,
        public ?int $available,
        public ?float $price,
        public ?int $minStay,
        public ?int $maxStay,
        public ?bool $closedOnArrival,
        public ?bool $closedOnDeparture,
        public ?bool $stopSell
    ) {
    }

    public static function map(array $data): self
    {
        return new self(
            date: Carbon::parse($data['@attributes']['date']),
            available: isset($data['available']) ? (int) $data['available'] : null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            minStay: isset($data['min_stay']) ? (int) $data['min_stay'] : null,
            maxStay: isset($data['max_stay']) ? (int) $data['max_stay'] : null,
            closedOnArrival: isset($data['closed_on_arrival']) ? (bool) $data['closed_on_arrival'] : null,
            closedOnDeparture: isset($data['closed_on_departure']) ? (bool) $data['closed_on_departure'] : null,
            stopSell: isset($data['stop_sell']) ? (bool) $data['stop_sell'] : null
        );
    }
}
