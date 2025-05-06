<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Carbon\Carbon; // Keep Carbon
use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

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
        $sourceData = $infoData instanceof Collection ? $infoData : new Collection($infoData);
        $attributes = new Collection($sourceData->get('@attributes', []));

        $day = null;
        if ($dayStr = $attributes->get('day')) {
            try {
                $day = Carbon::parse($dayStr);
            } catch (\Exception $e) {
                // error_log("Failed to parse day: {$dayStr}");
            }
        }
        $modified = null;
        if ($modStr = $attributes->get('modified')) {
            try {
                $modified = Carbon::parse($modStr);
            } catch (\Exception $e) {
                // error_log("Failed to parse modified: {$modStr}");
            }
        }

        return new self(
            day: $day ?? Carbon::create(1970, 1, 1), // Default for parse failure
            season: (string) ($attributes->get('season', '')),
            modified: $modified ?? Carbon::create(1970, 1, 1), // Default for parse failure
            available: (bool) ($sourceData->get('available') ?? false),
            stayMinimum: (int) ($sourceData->get('stay_minimum') ?? 0),
            rate: CalendarRate::fromXml(new Collection($sourceData->get('rate', [])))
        );
    }
}
