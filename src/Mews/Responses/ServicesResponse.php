<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Service;

class ServicesResponse
{
    /**
     * @param Collection<int, Service> $items
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['Services'] ?? [])
                ->map(fn($item) => Service::map($item)),
            cursor: $data['Cursor'] ?? null
        );
    }
}
