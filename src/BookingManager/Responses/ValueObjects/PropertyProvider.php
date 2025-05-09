<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



class PropertyProvider
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name
    ) {}

    public static function fromXml(array $data): self
    {
        $attributes = isset($data['@attributes']) ? $data['@attributes'] : $data;
        return new self(
            id: (int)($attributes['id'] ?? 0),
            code: (string)($attributes['code'] ?? ''),
            name: (string)($attributes['name'] ?? '')
        );
    }
}
