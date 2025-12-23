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
        // Additional fields from API
        public readonly ?string $actualStartUtc,
        public readonly ?string $actualEndUtc,
        public readonly ?int $adultCount,
        public readonly ?int $childCount,
        public readonly ?bool $assignedResourceLocked,
        public readonly ?string $bookerId,
        public readonly ?string $businessSegmentId,
        public readonly ?string $cancellationReason,
        public readonly ?string $channelManager,
        public readonly ?string $channelManagerNumber,
        public readonly ?string $channelManagerGroupNumber,
        public readonly ?string $channelNumber,
        public readonly ?string $companyId,
        public readonly ?string $creditCardId,
        public readonly ?string $groupId,
        public readonly ?array $options,
        public readonly ?string $origin,
        public readonly ?string $purpose,
        public readonly ?string $releaseUtc,
        public readonly ?string $requestedCategoryId,
        public readonly ?string $travelAgencyId,
        public readonly ?string $voucherCode,
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
                // Additional fields from API
                actualStartUtc: $data['ActualStartUtc'] ?? null,
                actualEndUtc: $data['ActualEndUtc'] ?? null,
                adultCount: $data['AdultCount'] ?? null,
                childCount: $data['ChildCount'] ?? null,
                assignedResourceLocked: $data['AssignedResourceLocked'] ?? null,
                bookerId: $data['BookerId'] ?? null,
                businessSegmentId: $data['BusinessSegmentId'] ?? null,
                cancellationReason: $data['CancellationReason'] ?? null,
                channelManager: $data['ChannelManager'] ?? null,
                channelManagerNumber: $data['ChannelManagerNumber'] ?? null,
                channelManagerGroupNumber: $data['ChannelManagerGroupNumber'] ?? null,
                channelNumber: $data['ChannelNumber'] ?? null,
                companyId: $data['CompanyId'] ?? null,
                creditCardId: $data['CreditCardId'] ?? null,
                groupId: $data['GroupId'] ?? null,
                options: $data['Options'] ?? null,
                origin: $data['Origin'] ?? null,
                purpose: $data['Purpose'] ?? null,
                releaseUtc: $data['ReleaseUtc'] ?? null,
                requestedCategoryId: $data['RequestedCategoryId'] ?? null,
                travelAgencyId: $data['TravelAgencyId'] ?? null,
                voucherCode: $data['VoucherCode'] ?? null,
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Reservation: {$e->getMessage()}", 0, $e);
        }
    }
}
