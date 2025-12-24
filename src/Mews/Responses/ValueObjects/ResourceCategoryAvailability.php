<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class ResourceCategoryAvailability
{
    /**
     * @param string $resourceCategoryId
     * @param array<string, array<int, int>> $metrics
     */
    public function __construct(
        public readonly string $resourceCategoryId,
        public readonly array $metrics,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                resourceCategoryId: $data['ResourceCategoryId'] ?? throw new \InvalidArgumentException('ResourceCategoryId is required'),
                metrics: $data['Metrics'] ?? [],
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map ResourceCategoryAvailability: {$e->getMessage()}", 0, $e);
        }
    }
}

