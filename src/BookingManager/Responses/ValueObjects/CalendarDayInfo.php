<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Carbon\Carbon;

class CalendarDayInfo
{
    public function __construct(
        public readonly Carbon $day,
        public readonly string $season,
        public readonly Carbon $modified,
        public readonly ?int $available, // Changed from bool to ?int
        public readonly int $stayMinimum,
        public readonly CalendarRate $rate,
        public readonly ?int $maxStay, // Added
        public readonly ?bool $closedOnArrival, // Added
        public readonly ?bool $closedOnDeparture, // Added
        public readonly ?bool $stopSell // Added
    ) {}

    public static function fromXml(array $infoData): self
    {
        $attributes = isset($infoData['@attributes']) ? $infoData['@attributes'] : [];
        $day = null;
        if (isset($attributes['day'])) {
            try {
                $day = Carbon::parse($attributes['day']);
            } catch (\Exception $e) {}
        }
        $modified = null;
        if (isset($attributes['modified'])) {
            try {
                $modified = Carbon::parse($attributes['modified']);
            } catch (\Exception $e) {}
        }
        return new self(
            day: $day ?? Carbon::create(1970, 1, 1),
            season: (string)($attributes['season'] ?? ''),
            modified: $modified ?? Carbon::create(1970, 1, 1),
            available: isset($infoData['available']) ? (int)$infoData['available'] : null, // Changed parsing
            stayMinimum: (int)($infoData['stay_minimum'] ?? 0),
            rate: CalendarRate::fromXml(isset($infoData['rate']) ? $infoData['rate'] : []),
            maxStay: isset($infoData['max_stay']) ? (int)$infoData['max_stay'] : null, // Added parsing
            closedOnArrival: isset($infoData['closed_on_arrival']) ? (bool)(int)$infoData['closed_on_arrival'] : null, // Added parsing, assumes 0/1
            closedOnDeparture: isset($infoData['closed_on_departure']) ? (bool)(int)$infoData['closed_on_departure'] : null, // Added parsing, assumes 0/1
            stopSell: isset($infoData['stop_sell']) ? (bool)(int)$infoData['stop_sell'] : null // Added parsing, assumes 0/1
        );
    }
}
