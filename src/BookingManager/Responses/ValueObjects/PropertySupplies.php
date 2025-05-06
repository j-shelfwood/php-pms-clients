<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PropertySupplies
{
    public function __construct(
        public readonly bool $coffee,
        public readonly bool $tea,
        public readonly bool $milk,
        public readonly bool $sugar,
        public readonly bool $dishwasherTablets
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        return new self(
            coffee: (bool) Arr::get($data, 'coffee', false),
            tea: (bool) Arr::get($data, 'tea', false),
            milk: (bool) Arr::get($data, 'milk', false),
            sugar: (bool) Arr::get($data, 'sugar', false),
            dishwasherTablets: (bool) Arr::get($data, 'dishwasher_tablets', false)
        );
    }
}
