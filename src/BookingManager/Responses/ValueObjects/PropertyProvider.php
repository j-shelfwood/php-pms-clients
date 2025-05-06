<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PropertyProvider
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $attributes = Arr::get($data, '@attributes', $data); // Handle direct attributes or nested under @attributes

        return new self(
            id: (int) ($attributes['id'] ?? 0),
            code: (string) ($attributes['code'] ?? ''),
            name: (string) ($attributes['name'] ?? '')
        );
    }
}
