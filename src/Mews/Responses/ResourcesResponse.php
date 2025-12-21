<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;

class ResourcesResponse
{
    public function __construct(
        public readonly array $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: array_map(
                fn($item) => Resource::map($item),
                $data['Resources'] ?? []
            ),
            cursor: $data['Cursor'] ?? null
        );
    }
}
