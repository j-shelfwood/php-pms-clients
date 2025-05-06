<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

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
        $getText = function ($key) use ($data) {
            $value = $data instanceof Collection ? $data->get($key) : ($data[$key] ?? null);
            if (is_array($value)) {
                return (string) ($value['#text'] ?? '');
            }
            return (string) ($value ?? '');
        };

        return new self(
            short: $getText('short'),
            full: $getText('full'),
            area: $getText('area'),
            arrival: $getText('arrival'),
            tac: $getText('tac')
        );
    }
}
