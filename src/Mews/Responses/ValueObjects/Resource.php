<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Enums\ResourceState;

class Resource
{
    /**
     * @param string $id
     * @param string $enterpriseId
     * @param string|null $serviceId
     * @param bool $isActive
     * @param string $name
     * @param string|null $parentResourceId
     * @param ResourceState $state
     * @param array<string, mixed> $data
     * @param string $createdUtc
     * @param string $updatedUtc
     * @param array<string, string>|null $descriptions
     * @param array<string, string>|null $directions
     * @param array<string, string>|null $externalNames
     */
    public function __construct(
        public readonly string $id,
        public readonly string $enterpriseId,
        public readonly ?string $serviceId,
        public readonly bool $isActive,
        public readonly string $name,
        public readonly ?string $parentResourceId,
        public readonly ResourceState $state,
        public readonly array $data,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
        // Additional fields from API
        public readonly ?array $descriptions,
        public readonly ?array $directions,
        public readonly ?array $externalNames,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                enterpriseId: $data['EnterpriseId'] ?? throw new \InvalidArgumentException('EnterpriseId required'),
                serviceId: $data['ServiceId'] ?? null,
                isActive: $data['IsActive'] ?? true,
                name: $data['Name'] ?? throw new \InvalidArgumentException('Name required'),
                parentResourceId: $data['ParentResourceId'] ?? null,
                state: isset($data['State']) ? ResourceState::from($data['State']) : ResourceState::Clean,
                data: $data['Data'] ?? [],
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
                // Additional fields from API
                descriptions: $data['Descriptions'] ?? null,
                directions: $data['Directions'] ?? null,
                externalNames: $data['ExternalNames'] ?? null,
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Resource: {$e->getMessage()}", 0, $e);
        }
    }
}
