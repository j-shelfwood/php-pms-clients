<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;

class GetAvailabilityPayload
{
    public function __construct(
        public readonly string $serviceId,
        public readonly Carbon $firstTimeUnitStartUtc,
        public readonly Carbon $lastTimeUnitStartUtc,
        public readonly ?array $resourceCategoryIds = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->serviceId)) {
            throw new \InvalidArgumentException('ServiceId is required');
        }

        if ($this->firstTimeUnitStartUtc->gte($this->lastTimeUnitStartUtc)) {
            throw new \InvalidArgumentException('FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'ServiceId' => $this->serviceId,
            'FirstTimeUnitStartUtc' => $this->firstTimeUnitStartUtc->toIso8601String(),
            'LastTimeUnitStartUtc' => $this->lastTimeUnitStartUtc->toIso8601String(),
            'ResourceCategoryIds' => $this->resourceCategoryIds,
        ], fn($value) => $value !== null);
    }
}
