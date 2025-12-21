<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class Restriction
{
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly string $resourceCategoryId,
        public readonly string $startUtc,
        public readonly string $endUtc,
        public readonly ?int $minimumStay,
        public readonly ?int $maximumStay,
        public readonly ?string $minAdvance,
        public readonly ?string $maxAdvance,
        public readonly string $type,
        public readonly array $conditions,
        public readonly array $exceptions,
        public readonly string $origin,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                serviceId: $data['ServiceId'] ?? throw new \InvalidArgumentException('ServiceId required'),
                resourceCategoryId: $data['ResourceCategoryId'] ?? throw new \InvalidArgumentException('ResourceCategoryId required'),
                startUtc: $data['StartUtc'] ?? throw new \InvalidArgumentException('StartUtc required'),
                endUtc: $data['EndUtc'] ?? throw new \InvalidArgumentException('EndUtc required'),
                minimumStay: $data['MinimumStay'] ?? null,
                maximumStay: $data['MaximumStay'] ?? null,
                minAdvance: $data['MinAdvance'] ?? null,
                maxAdvance: $data['MaxAdvance'] ?? null,
                type: $data['Type'] ?? 'Stay',
                conditions: $data['Conditions'] ?? [],
                exceptions: $data['Exceptions'] ?? [],
                origin: $data['Origin'] ?? 'User',
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Restriction: {$e->getMessage()}", 0, $e);
        }
    }
}
