<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class ResourceCategory
{
    /**
     * @param string $id
     * @param string $serviceId
     * @param bool $isActive
     * @param string $type
     * @param array<string, string> $names
     * @param array<string, string>|null $shortNames
     * @param array<string, string>|null $description
     * @param int $ordering
     * @param int $capacity
     * @param int $extraCapacity
     * @param array<int, string> $classifications
     * @param string $createdUtc
     * @param string $updatedUtc
     */
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly bool $isActive,
        public readonly string $type,
        public readonly array $names,
        public readonly ?array $shortNames,
        public readonly ?array $description,
        public readonly int $ordering,
        public readonly int $capacity,
        public readonly int $extraCapacity,
        public readonly array $classifications,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                serviceId: $data['ServiceId'] ?? throw new \InvalidArgumentException('ServiceId required'),
                isActive: $data['IsActive'] ?? true,
                type: $data['Type'] ?? 'Room',
                names: $data['Names'] ?? [],
                shortNames: $data['ShortNames'] ?? null,
                description: $data['Description'] ?? null,
                ordering: $data['Ordering'] ?? 0,
                capacity: $data['Capacity'] ?? 0,
                extraCapacity: $data['ExtraCapacity'] ?? 0,
                classifications: $data['Classifications'] ?? [],
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map ResourceCategory: {$e->getMessage()}", 0, $e);
        }
    }
}
