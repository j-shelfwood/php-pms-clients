<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class ResourceCategoryAssignment
{
    public function __construct(
        public readonly string $id,
        public readonly string $resourceId,
        public readonly string $categoryId,
        public readonly bool $isActive,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                resourceId: $data['ResourceId'] ?? throw new \InvalidArgumentException('ResourceId required'),
                categoryId: $data['CategoryId'] ?? throw new \InvalidArgumentException('CategoryId required'),
                isActive: $data['IsActive'] ?? true,
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map ResourceCategoryAssignment: {$e->getMessage()}", 0, $e);
        }
    }
}
