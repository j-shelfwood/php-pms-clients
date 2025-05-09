<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

class PropertyImage
{

    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly ?string $modified,
        public readonly ?string $description
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    /**
     * @param array<string, mixed> $data Parsed XML for a single <image> tag
     */
    public static function fromXml(array $data): self
    {
        $attributes = $data['@attributes'] ?? [];

        $description = null;
        if (isset($data['#text']) && is_string($data['#text'])) {
            $description = trim($data['#text']);
        } elseif (isset($data[0]) && is_string($data[0])) {
            $description = trim($data[0]);
        }

        return new self(
            name: (string)($attributes['name'] ?? ''),
            url: (string)($attributes['url'] ?? ''),
            modified: isset($attributes['modified']) ? (string)$attributes['modified'] : null,
            description: $description
        );
    }
}
