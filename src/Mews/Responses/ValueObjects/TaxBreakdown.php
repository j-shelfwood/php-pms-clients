<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class TaxBreakdown
{
    /**
     * @param array<int, TaxBreakdownItem> $items
     */
    public function __construct(
        public readonly array $items,
    ) {}

    public static function map(array $data): self
    {
        try {
            $items = array_map(
                fn($item) => TaxBreakdownItem::map($item),
                $data['Items'] ?? []
            );

            return new self($items);
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map TaxBreakdown: {$e->getMessage()}", 0, $e);
        }
    }
}
