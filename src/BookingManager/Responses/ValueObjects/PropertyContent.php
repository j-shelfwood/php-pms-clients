<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PropertyContent
{
    public function __construct(
        public readonly string $short,
        public readonly string $full,
        public readonly string $area,
        public readonly string $arrival,
        public readonly string $tac // Terms and Conditions
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        // Helper to extract CDATA or text content, avoid array-to-string conversion
        $getText = fn ($key) => (
            is_array(Arr::get($data, $key))
                ? (string) (Arr::get($data, "{$key}.#text") ?? '')
                : (string) (Arr::get($data, $key, ''))
        );

        return new self(
            short: $getText('short'),
            full: $getText('full'),
            area: $getText('area'),
            arrival: $getText('arrival'),
            tac: $getText('tac')
        );
    }
}
