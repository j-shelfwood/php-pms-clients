<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;

class ResourcesResponse
{
    /**
     * @param Collection<int, Resource> $items
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['Resources'] ?? [])
                ->map(fn($item) => Resource::map($item)),
            cursor: $data['Cursor'] ?? null
        );
    }
}
