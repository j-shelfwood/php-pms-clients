<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Enums\AgeClassification;

class AgeCategory
{
    /**
     * @param string $id
     * @param string $serviceId
     * @param AgeClassification $classification
     * @param int $minimalAge
     * @param int|null $maximalAge
     * @param array<string, string> $names
     * @param array<string, string>|null $shortNames
     * @param bool $isActive
     * @param string $createdUtc
     * @param string $updatedUtc
     */
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly AgeClassification $classification,
        public readonly int $minimalAge,
        public readonly ?int $maximalAge,
        public readonly array $names,
        public readonly ?array $shortNames,
        public readonly bool $isActive,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                serviceId: $data['ServiceId'] ?? throw new \InvalidArgumentException('ServiceId required'),
                classification: AgeClassification::from($data['Classification'] ?? throw new \InvalidArgumentException('Classification required')),
                minimalAge: $data['MinimalAge'] ?? 0,
                maximalAge: $data['MaximalAge'] ?? null,
                names: $data['Names'] ?? [],
                shortNames: $data['ShortNames'] ?? null,
                isActive: $data['IsActive'] ?? true,
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map AgeCategory: {$e->getMessage()}", 0, $e);
        }
    }
}
