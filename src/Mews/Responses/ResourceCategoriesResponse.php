<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategory;

class ResourceCategoriesResponse
{
    /**
     * @param Collection<int, ResourceCategory> $items
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['ResourceCategories'] ?? [])
                ->map(fn($item) => ResourceCategory::map($item)),
            cursor: $data['Cursor'] ?? null
        );
    }
}
