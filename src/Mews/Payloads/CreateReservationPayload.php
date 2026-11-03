<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

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
        public readonly ReservationState $state = ReservationState::Confirmed,
        public readonly ?string $notes = null,
        public readonly ?Carbon $releaseUtc = null,
        /** @var TimeUnitPrice[]|null Per-night prices; Mews `TimeUnitPrices` */
        public readonly ?array $timeUnitPrices = null,
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

        if ($this->state === ReservationState::Optional && $this->releaseUtc === null) {
            throw new \InvalidArgumentException('ReleaseUtc required for Optional reservations');
        }

        if ($this->timeUnitPrices !== null) {
            $this->validateTimeUnitPrices($this->timeUnitPrices);
        }
    }

    private function validateTimeUnitPrices(array $prices): void
    {
        if ($prices === []) {
            throw new \InvalidArgumentException('TimeUnitPrices cannot be an empty array; pass null to omit');
        }

        $nights = $this->nights();
        $seen = [];

        foreach ($prices as $price) {
            if (! $price instanceof TimeUnitPrice) {
                throw new \InvalidArgumentException('TimeUnitPrices must contain only TimeUnitPrice instances');
            }

            if ($price->index < 0 || $price->index >= $nights) {
                throw new \InvalidArgumentException(sprintf(
                    'TimeUnitPrice Index %d out of range 0..%d',
                    $price->index,
                    $nights - 1,
                ));
            }

            if (isset($seen[$price->index])) {
                throw new \InvalidArgumentException(sprintf('Duplicate TimeUnitPrice Index %d', $price->index));
            }

            $seen[$price->index] = true;
        }
    }

    /**
     * Number of nights as the difference between the UTC calendar dates of
     * StartUtc and EndUtc.
     */
    private function nights(): int
    {
        $start = $this->startUtc->copy()->utc()->startOfDay();
        $end = $this->endUtc->copy()->utc()->startOfDay();

        return (int) $start->diffInDays($end);
    }

    public function toArray(): array
    {
        return array_filter([
            'CustomerId' => $this->customerId,
            'RateId' => $this->rateId,
            'StartUtc' => $this->startUtc->copy()->utc()->toIso8601ZuluString(),
            'EndUtc' => $this->endUtc->copy()->utc()->toIso8601ZuluString(),
            'PersonCounts' => $this->personCounts,
            'RequestedCategoryId' => $this->requestedCategoryId,
            'State' => $this->state->value,
            'Notes' => $this->notes,
            'ReleasedUtc' => $this->releaseUtc?->copy()->utc()->toIso8601ZuluString(),
            'TimeUnitPrices' => $this->timeUnitPrices === null
                ? null
                : array_map(fn (TimeUnitPrice $price) => $price->toArray(), array_values($this->timeUnitPrices)),
        ], fn($value) => $value !== null);
    }
}
