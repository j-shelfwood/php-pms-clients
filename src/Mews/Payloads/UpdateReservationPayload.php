<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

class UpdateReservationPayload
{
    public function __construct(
        public readonly string $reservationId,
        public readonly ?Carbon $startUtc = null,
        public readonly ?Carbon $endUtc = null,
        public readonly ?array $personCounts = null,
        public readonly ?string $requestedCategoryId = null,
        public readonly ?ReservationState $state = null,
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

        if ($this->state === ReservationState::Optional && $this->releaseUtc === null) {
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
            'State' => $this->state?->value,
            'Notes' => $this->notes,
            'ReleaseUtc' => $this->releaseUtc?->toIso8601String(),
        ], fn($value) => $value !== null);
    }
}
