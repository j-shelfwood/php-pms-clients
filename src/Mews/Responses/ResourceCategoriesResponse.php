<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategory;

class ResourceCategoriesResponse
{
    public function __construct(
        public readonly array $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: array_map(
                fn($item) => ResourceCategory::map($item),
                $data['ResourceCategories'] ?? []
            ),
            cursor: $data['Cursor'] ?? null
        );
    }
}
