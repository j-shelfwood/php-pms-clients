<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;

class UpdateReservationPayload
{
    public function __construct(
        public readonly string $reservationId,
        public readonly ?Carbon $startUtc = null,
        public readonly ?Carbon $endUtc = null,
        public readonly ?array $personCounts = null,
        public readonly ?string $requestedCategoryId = null,
        public readonly ?string $state = null,
        public readonly ?string $notes = null,
        public readonly ?Carbon $releaseUtc = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->reservationId)) {
            throw new \InvalidArgumentException('ReservationId is required');
        }

        if ($this->startUtc !== null && $this->endUtc !== null && $this->startUtc->gte($this->endUtc)) {
            throw new \InvalidArgumentException('StartUtc must be before EndUtc');
        }

        if ($this->state === 'Optional' && $this->releaseUtc === null) {
            throw new \InvalidArgumentException('ReleaseUtc required for Optional state');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'ReservationId' => $this->reservationId,
            'StartUtc' => $this->startUtc?->toIso8601String(),
            'EndUtc' => $this->endUtc?->toIso8601String(),
            'PersonCounts' => $this->personCounts,
            'RequestedCategoryId' => $this->requestedCategoryId,
            'State' => $this->state,
            'Notes' => $this->notes,
            'ReleaseUtc' => $this->releaseUtc?->toIso8601String(),
        ], fn($value) => $value !== null);
    }
}
