<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAssignment;

class ResourceCategoryAssignmentsResponse
{
    /**
     * @param Collection<int, ResourceCategoryAssignment> $items
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['ResourceCategoryAssignments'] ?? [])
                ->map(fn($item) => ResourceCategoryAssignment::map($item)),
            cursor: $data['Cursor'] ?? null
        );
    }
}
