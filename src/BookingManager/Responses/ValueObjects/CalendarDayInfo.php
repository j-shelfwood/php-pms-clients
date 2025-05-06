<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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

    public static function fromXml(Collection|array $infoData): self
    {
        $attributes = $infoData->get('@attributes', []);

        // Handle potential missing date values gracefully
        $day = null;
        if ($dayStr = $attributes['day'] ?? null) {
            try {
                $day = Carbon::parse($dayStr);
            } catch (\Exception $e) {
            }
        }
        $modified = null;
        if ($modStr = $attributes['modified'] ?? null) {
            try {
                $modified = Carbon::parse($modStr);
            } catch (\Exception $e) {
            }
        }

        return new self(
            day: $day ?? Carbon::create(1970, 1, 1), // Provide a default/invalid date if parsing fails
            season: (string) ($attributes['season'] ?? ''),
            modified: $modified ?? Carbon::create(1970, 1, 1), // Provide a default/invalid date
            available: (bool) ($infoData->get('available') ?? false),
            stayMinimum: (int) ($infoData->get('stay_minimum') ?? 0),
            rate: CalendarRate::fromXml(collect($infoData->get('rate', [])))
        );
    }
}
