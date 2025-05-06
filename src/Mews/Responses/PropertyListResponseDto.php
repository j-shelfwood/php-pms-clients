<?php

namespace Domain\Connections\Mews\Dtos;

use Domain\Dtos\PropertyInfo;
use Domain\Dtos\PropertyListResponse;

class PropertyListResponseDto
{
    private array $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $raw): self
    {
        // 'data' key holds the list of bookable objects
        return new self($raw['data'] ?? []);
    }

    public function toDomain(): PropertyListResponse
    {
        $collection = collect($this->items)
            ->map(fn (array $item) => PropertyInfo::fromArray($item));

        return new PropertyListResponse($collection);
    }
}
