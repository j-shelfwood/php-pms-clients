<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class CalendarChange
{
    public function __construct(
        public readonly int $propertyId,
        /** @var Collection<int, string> */
        public readonly Collection $months // Collection of 'YYYY-MM' strings
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $sourceData = $data instanceof Collection ? $data : new Collection($data);
        $attributes = new Collection($sourceData->get('@attributes', []));

        $monthsString = (string) $attributes->get('months', '');
        $months = !empty($monthsString) ? collect(explode(',', $monthsString)) : collect();

        return new self(
            propertyId: (int) $attributes->get('id', 0),
            months: $months
        );
    }
}
