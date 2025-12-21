<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AgeCategory;

class AgeCategoriesResponse
{
    public function __construct(
        public readonly array $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: array_map(
                fn($item) => AgeCategory::map($item),
                $data['AgeCategories'] ?? []
            ),
            cursor: $data['Cursor'] ?? null
        );
    }
}
