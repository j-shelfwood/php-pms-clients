<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class PropertyService
{
    public function __construct(
        public readonly bool $linen,
        public readonly bool $towels,
        public readonly bool $cleaning
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        return new self(
            linen: (bool) ($data instanceof Collection ? $data->get('linen') : ($data['linen'] ?? false)),
            towels: (bool) ($data instanceof Collection ? $data->get('towels') : ($data['towels'] ?? false)),
            cleaning: (bool) ($data instanceof Collection ? $data->get('cleaning') : ($data['cleaning'] ?? false))
        );
    }
}
