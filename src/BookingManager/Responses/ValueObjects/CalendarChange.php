<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CalendarChange
{
    public function __construct(
        public readonly int $propertyId,
        /** @var Collection<int, string> */
        public readonly Collection $months // Collection of 'YYYY-MM' strings
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $attributes = Arr::get($data, '@attributes', []);
        $monthsString = (string) Arr::get($attributes, 'months', '');
        $months = ! empty($monthsString) ? collect(explode(',', $monthsString)) : collect();

        return new self(
            propertyId: (int) Arr::get($attributes, 'id', 0),
            months: $months
        );
    }
}
