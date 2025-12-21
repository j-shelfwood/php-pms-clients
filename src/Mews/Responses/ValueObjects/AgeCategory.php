<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class AgeCategory
{
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly string $classification,
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
                classification: $data['Classification'] ?? throw new \InvalidArgumentException('Classification required'),
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
