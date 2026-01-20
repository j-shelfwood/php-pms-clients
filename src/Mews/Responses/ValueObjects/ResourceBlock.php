<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Carbon\Carbon;

/**
 * Mews Resource Block Value Object
 *
 * Represents a calendar availability block or restriction on a resource.
 * These blocks can be manual (set by property managers) or automatic (created by reservations).
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/resourceblocks
 */
final readonly class ResourceBlock
{
    public function __construct(
        public string $id,
        public string $serviceId,
        public ?string $assignedResourceId,
        public Carbon $startUtc,
        public Carbon $endUtc,
        public string $type,
        public ?string $reservationId = null,
    ) {
    }

    /**
     * Create ResourceBlock from Mews API response data
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['Id'],
            serviceId: $data['ServiceId'],
            assignedResourceId: $data['AssignedResourceId'] ?? null,
            startUtc: Carbon::parse($data['StartUtc']),
            endUtc: Carbon::parse($data['EndUtc']),
            type: $data['Type'],
            reservationId: $data['ReservationId'] ?? null,
        );
    }
}
