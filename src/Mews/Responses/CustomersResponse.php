<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;

class CustomersResponse
{
    public function __construct(
        public readonly array $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: array_map(
                fn($item) => Customer::map($item),
                $data['Customers'] ?? []
            ),
            cursor: $data['Cursor'] ?? null
        );
    }
}
