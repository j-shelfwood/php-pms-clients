<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Carbon\Carbon;

class CalendarDayInfo
{
    public function __construct(
        public readonly Carbon $day,
        public readonly string $season,
        public readonly Carbon $modified,
        public readonly bool $available,
        public readonly int $stayMinimum,
        public readonly CalendarRate $rate
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
            available: (bool)($infoData['available'] ?? false),
            stayMinimum: (int)($infoData['stay_minimum'] ?? 0),
            rate: CalendarRate::fromXml(isset($infoData['rate']) ? $infoData['rate'] : [])
        );
    }
}
