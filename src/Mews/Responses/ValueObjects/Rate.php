<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Enums\RateType;

class Rate
{
    /**
     * @param string $id
     * @param string $serviceId
     * @param string|null $groupId
     * @param string|null $accountingCategoryId
     * @param bool $isActive
     * @param bool $isPublic
     * @param RateType $type
     * @param array<string, string> $names
     * @param array<string, string>|null $shortNames
     * @param array<string, string>|null $description
     * @param string|null $baseRateId
     * @param bool $isPrivate
     * @param string|null $externalIdentifier
     * @param string $createdUtc
     * @param string $updatedUtc
     */
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly ?string $groupId,
        public readonly ?string $accountingCategoryId,
        public readonly bool $isActive,
        public readonly bool $isPublic,
        public readonly RateType $type,
        public readonly array $names,
        public readonly ?array $shortNames,
        public readonly ?array $description,
        public readonly ?string $baseRateId,
        public readonly bool $isPrivate,
        public readonly ?string $externalIdentifier,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                serviceId: $data['ServiceId'] ?? throw new \InvalidArgumentException('ServiceId required'),
                groupId: $data['GroupId'] ?? null,
                accountingCategoryId: $data['AccountingCategoryId'] ?? null,
                isActive: $data['IsActive'] ?? true,
                isPublic: $data['IsPublic'] ?? true,
                type: isset($data['Type']) ? RateType::from($data['Type']) : RateType::Public,
                names: $data['Names'] ?? [],
                shortNames: $data['ShortNames'] ?? null,
                description: $data['Description'] ?? null,
                baseRateId: $data['BaseRateId'] ?? null,
                isPrivate: $data['IsPrivate'] ?? false,
                externalIdentifier: $data['ExternalIdentifier'] ?? null,
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Rate: {$e->getMessage()}", 0, $e);
        }
    }
}
