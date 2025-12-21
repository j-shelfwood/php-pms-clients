<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class Resource
{
    public function __construct(
        public readonly string $id,
        public readonly string $enterpriseId,
        public readonly ?string $serviceId,
        public readonly bool $isActive,
        public readonly string $name,
        public readonly ?string $parentResourceId,
        public readonly string $state,
        public readonly array $data,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
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
                state: $data['State'] ?? 'Clean',
                data: $data['Data'] ?? [],
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Resource: {$e->getMessage()}", 0, $e);
        }
    }
}
