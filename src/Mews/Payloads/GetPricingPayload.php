<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;

class GetPricingPayload
{
    public function __construct(
        public readonly string $rateId,
        public readonly Carbon $firstTimeUnitStartUtc,
        public readonly Carbon $lastTimeUnitStartUtc,
        public readonly ?array $occupancyConfiguration = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->rateId)) {
            throw new \InvalidArgumentException('RateId is required');
        }

        if ($this->firstTimeUnitStartUtc->gte($this->lastTimeUnitStartUtc)) {
            throw new \InvalidArgumentException('FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'RateId' => $this->rateId,
            'FirstTimeUnitStartUtc' => $this->firstTimeUnitStartUtc->toIso8601String(),
            'LastTimeUnitStartUtc' => $this->lastTimeUnitStartUtc->toIso8601String(),
            'OccupancyConfiguration' => $this->occupancyConfiguration,
        ], fn($value) => $value !== null);
    }
}
