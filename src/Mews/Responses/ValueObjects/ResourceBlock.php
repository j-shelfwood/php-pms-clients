<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Carbon\Carbon;

/**
 * Mews Resource Block Value Object
 *
 * Represents a calendar availability block or restriction on a resource.
 * These blocks can be manual (set by property managers) or automatic (created by system).
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/resourceblocks
 */
final readonly class ResourceBlock
{
    public function __construct(
        public string $id,
        public string $enterpriseId,
        public string $assignedResourceId,
        public bool $isActive,
        public string $type,
        public Carbon $startUtc,
        public Carbon $endUtc,
        public Carbon $createdUtc,
        public Carbon $updatedUtc,
        public ?Carbon $deletedUtc,
        public string $name,
        public ?string $notes,
    ) {
    }

    /**
     * Create ResourceBlock from Mews API response data
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['Id'],
            enterpriseId: $data['EnterpriseId'],
            assignedResourceId: $data['AssignedResourceId'],
            isActive: $data['IsActive'],
            type: $data['Type'],
            startUtc: Carbon::parse($data['StartUtc']),
            endUtc: Carbon::parse($data['EndUtc']),
            createdUtc: Carbon::parse($data['CreatedUtc']),
            updatedUtc: Carbon::parse($data['UpdatedUtc']),
            deletedUtc: isset($data['DeletedUtc']) && $data['DeletedUtc'] ? Carbon::parse($data['DeletedUtc']) : null,
            name: $data['Name'],
            notes: $data['Notes'] ?? null,
        );
    }
}
