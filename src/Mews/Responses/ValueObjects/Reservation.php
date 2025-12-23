<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

class Reservation
{
    /**
     * @param string $id
     * @param string $serviceId
     * @param string $accountId
     * @param string $number
     * @param ReservationState $state
     * @param array<string, int> $personCounts
     * @param string $scheduledStartUtc
     * @param string $scheduledEndUtc
     * @param string|null $assignedResourceId
     * @param string $rateId
     * @param string|null $notes
     * @param string $createdUtc
     * @param string $updatedUtc
     * @param string|null $actualStartUtc
     * @param string|null $actualEndUtc
     * @param int|null $adultCount
     * @param int|null $childCount
     * @param bool|null $assignedResourceLocked
     * @param string|null $bookerId
     * @param string|null $businessSegmentId
     * @param string|null $cancellationReason
     * @param string|null $channelManager
     * @param string|null $channelManagerNumber
     * @param string|null $channelManagerGroupNumber
     * @param string|null $channelNumber
     * @param string|null $companyId
     * @param string|null $creditCardId
     * @param string|null $groupId
     * @param array<string, mixed>|null $options
     * @param string|null $origin
     * @param string|null $purpose
     * @param string|null $releaseUtc
     * @param string|null $requestedCategoryId
     * @param string|null $travelAgencyId
     * @param string|null $voucherCode
     */
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly string $accountId,
        public readonly string $number,
        public readonly ReservationState $state,
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
                state: ReservationState::from($data['State'] ?? throw new \InvalidArgumentException('State required')),
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
