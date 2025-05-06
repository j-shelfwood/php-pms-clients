<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PropertyService
{
    public function __construct(
        public readonly bool $linen,
        public readonly bool $towels,
        public readonly bool $cleaning // Deprecated in docs, but present in mock
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        return new self(
            linen: (bool) Arr::get($data, 'linen', false),
            towels: (bool) Arr::get($data, 'towels', false),
            cleaning: (bool) Arr::get($data, 'cleaning', false)
        );
    }
}
