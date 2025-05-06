<?php

namespace Domain\Connections\BookingManager\Responses;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

// Note: This class was already extracted but might have been inside PropertyInfoResponse before.
// Ensure it's standalone and correctly namespaced.
class PropertyImageResponse
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $description,
        public readonly ?Carbon $modified
    ) {}

    public static function fromXml(array|\Illuminate\Support\Collection $data): self
    {
        return new self(
            name: (string) Arr::get($data, '@attributes.name', Arr::get($data, 'name', 'default.jpg')),
            url: (string) Arr::get($data, '@attributes.url', Arr::get($data, 'url', '')),
            description: (string) (Arr::get($data, '#text') ?? Arr::get($data, '', '')),
            modified: Arr::has($data, '@attributes.modified') ? Carbon::parse(Arr::get($data, '@attributes.modified')) : null
        );
    }

    // No toArray for strict DTO/VO unity
}
