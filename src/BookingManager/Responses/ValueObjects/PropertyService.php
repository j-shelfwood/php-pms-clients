<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



class PropertyService
{
    public function __construct(
        public readonly bool $linen,
        public readonly bool $towels,
        public readonly bool $cleaning
    ) {}

    public static function fromXml(array $data): self
    {
        return new self(
            linen: (bool)($data['linen'] ?? false),
            towels: (bool)($data['towels'] ?? false),
            cleaning: (bool)($data['cleaning'] ?? false)
        );
    }
}
