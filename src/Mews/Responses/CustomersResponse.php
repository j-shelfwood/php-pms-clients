<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;

class CustomersResponse
{
    /**
     * @param Collection<int, Customer> $items
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['Customers'] ?? [])
                ->map(fn($item) => Customer::map($item['Customer'] ?? $item)),
            cursor: $data['Cursor'] ?? null
        );
    }
}
