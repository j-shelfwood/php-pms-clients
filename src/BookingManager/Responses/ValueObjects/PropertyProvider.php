<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class PropertyProvider
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $attributes = $data instanceof Collection ? ($data->get('@attributes') ?? $data) : ($data['@attributes'] ?? $data);
        // If $attributes is still a Collection after get, convert to array for consistent access
        if ($attributes instanceof Collection) {
            $attributes = $attributes->all();
        }

        return new self(
            id: (int) ($attributes['id'] ?? 0),
            code: (string) ($attributes['code'] ?? ''),
            name: (string) ($attributes['name'] ?? '')
        );
    }
}
