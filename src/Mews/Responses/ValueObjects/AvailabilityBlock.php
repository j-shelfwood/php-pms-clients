<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class AvailabilityBlock
{
    /**
     * @param string $categoryId
     * @param array<int, int> $availabilities
     * @param array<int, int> $adjustments
     */
    public function __construct(
        public readonly string $categoryId,
        public readonly array $availabilities,
        public readonly array $adjustments,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                categoryId: $data['CategoryId'] ?? throw new \InvalidArgumentException('CategoryId is required'),
                availabilities: $data['Availabilities'] ?? [],
                adjustments: $data['Adjustments'] ?? [],
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map AvailabilityBlock: {$e->getMessage()}", 0, $e);
        }
    }
}
