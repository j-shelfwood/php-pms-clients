<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class Reservation
{
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly string $accountId,
        public readonly string $number,
        public readonly string $state,
        public readonly array $personCounts,
        public readonly string $scheduledStartUtc,
        public readonly string $scheduledEndUtc,
        public readonly ?string $assignedResourceId,
        public readonly string $rateId,
        public readonly ?string $notes,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                serviceId: $data['ServiceId'] ?? throw new \InvalidArgumentException('ServiceId required'),
                accountId: $data['AccountId'] ?? throw new \InvalidArgumentException('AccountId required'),
                number: $data['Number'] ?? throw new \InvalidArgumentException('Number required'),
                state: $data['State'] ?? throw new \InvalidArgumentException('State required'),
                personCounts: $data['PersonCounts'] ?? [],
                scheduledStartUtc: $data['ScheduledStartUtc'] ?? throw new \InvalidArgumentException('ScheduledStartUtc required'),
                scheduledEndUtc: $data['ScheduledEndUtc'] ?? throw new \InvalidArgumentException('ScheduledEndUtc required'),
                assignedResourceId: $data['AssignedResourceId'] ?? null,
                rateId: $data['RateId'] ?? throw new \InvalidArgumentException('RateId required'),
                notes: $data['Notes'] ?? null,
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Reservation: {$e->getMessage()}", 0, $e);
        }
    }
}
