<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Enums\ServiceType;

class Service
{
    /**
     * @param string $id
     * @param string $enterpriseId
     * @param bool $isActive
     * @param array<string, string> $names
     * @param array<string, string>|null $shortNames
     * @param array<string, string>|null $description
     * @param array<string, mixed> $options
     * @param array<string, mixed> $data
     * @param string|null $externalIdentifier
     * @param int $ordering
     * @param string $createdUtc
     * @param string $updatedUtc
     * @param string|null $name
     * @param ServiceType|null $type
     * @param string|null $startTime
     * @param string|null $endTime
     * @param array<int, mixed>|null $promotions
     */
    public function __construct(
        public readonly string $id,
        public readonly string $enterpriseId,
        public readonly bool $isActive,
        public readonly array $names,
        public readonly ?array $shortNames,
        public readonly ?array $description,
        public readonly array $options,
        public readonly array $data,
        public readonly ?string $externalIdentifier,
        public readonly int $ordering,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
        // Additional fields from API
        public readonly ?string $name,
        public readonly ?ServiceType $type,
        public readonly ?string $startTime,
        public readonly ?string $endTime,
        public readonly ?array $promotions,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                enterpriseId: $data['EnterpriseId'] ?? throw new \InvalidArgumentException('EnterpriseId required'),
                isActive: $data['IsActive'] ?? true,
                names: $data['Names'] ?? [],
                shortNames: $data['ShortNames'] ?? null,
                description: $data['Description'] ?? null,
                options: $data['Options'] ?? [],
                data: $data['Data'] ?? [],
                externalIdentifier: $data['ExternalIdentifier'] ?? null,
                ordering: $data['Ordering'] ?? 0,
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
                // Additional fields from API
                name: $data['Name'] ?? null,
                type: isset($data['Type']) ? ServiceType::from($data['Type']) : null,
                startTime: $data['StartTime'] ?? null,
                endTime: $data['EndTime'] ?? null,
                promotions: $data['Promotions'] ?? null,
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Service: {$e->getMessage()}", 0, $e);
        }
    }
}
