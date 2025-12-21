<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Rate;

class RatesResponse
{
    public function __construct(
        public readonly array $items,
        public readonly array $rateGroups = [],
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: array_map(
                fn($item) => Rate::map($item),
                $data['Rates'] ?? []
            ),
            rateGroups: $data['RateGroups'] ?? [],
            cursor: $data['Cursor'] ?? null
        );
    }
}
