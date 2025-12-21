<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;

class CreateReservationPayload
{
    public function __construct(
        public readonly string $serviceId,
        public readonly string $customerId,
        public readonly string $rateId,
        public readonly Carbon $startUtc,
        public readonly Carbon $endUtc,
        public readonly array $personCounts,
        public readonly ?string $requestedCategoryId = null,
        public readonly string $state = 'Confirmed',
        public readonly ?string $notes = null,
        public readonly ?Carbon $releaseUtc = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->personCounts)) {
            throw new \InvalidArgumentException('PersonCounts cannot be empty');
        }

        if ($this->startUtc->gte($this->endUtc)) {
            throw new \InvalidArgumentException('StartUtc must be before EndUtc');
        }

        if ($this->state === 'Optional' && $this->releaseUtc === null) {
            throw new \InvalidArgumentException('ReleaseUtc required for Optional reservations');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'CustomerId' => $this->customerId,
            'RateId' => $this->rateId,
            'StartUtc' => $this->startUtc->toIso8601String(),
            'EndUtc' => $this->endUtc->toIso8601String(),
            'PersonCounts' => $this->personCounts,
            'RequestedCategoryId' => $this->requestedCategoryId,
            'State' => $this->state,
            'Notes' => $this->notes,
            'ReleaseUtc' => $this->releaseUtc?->toIso8601String(),
        ], fn($value) => $value !== null);
    }
}
