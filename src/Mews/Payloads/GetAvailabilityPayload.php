<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

class GetAvailabilityPayload
{
    /**
     * @var array<int, ResourceAvailabilityMetricType>
     */
    public readonly array $metrics;

    public function __construct(
        public readonly string $serviceId,
        public readonly Carbon $firstTimeUnitStartUtc,
        public readonly Carbon $lastTimeUnitStartUtc,
        ?array $metrics = null,
    ) {
        $this->metrics = $this->normalizeMetrics($metrics ?? ResourceAvailabilityMetricType::cases());
        $this->validate();
    }

    /**
     * @param array<int, ResourceAvailabilityMetricType|string> $metrics
     * @return array<int, ResourceAvailabilityMetricType>
     */
    private function normalizeMetrics(array $metrics): array
    {
        return array_values(array_map(function ($metric) {
            if ($metric instanceof ResourceAvailabilityMetricType) {
                return $metric;
            }

            if (is_string($metric)) {
                return ResourceAvailabilityMetricType::from($metric);
            }

            throw new \InvalidArgumentException('Invalid metric type');
        }, $metrics));
    }

    private function validate(): void
    {
        if (empty($this->serviceId)) {
            throw new \InvalidArgumentException('ServiceId is required');
        }

        if ($this->firstTimeUnitStartUtc->gte($this->lastTimeUnitStartUtc)) {
            throw new \InvalidArgumentException('FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');
        }

        if (empty($this->metrics)) {
            throw new \InvalidArgumentException('Metrics cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'ServiceId' => $this->serviceId,
            'FirstTimeUnitStartUtc' => $this->firstTimeUnitStartUtc->copy()->utc()->toIso8601ZuluString(),
            'LastTimeUnitStartUtc' => $this->lastTimeUnitStartUtc->copy()->utc()->toIso8601ZuluString(),
            'Metrics' => array_map(fn (ResourceAvailabilityMetricType $metric) => $metric->value, $this->metrics),
        ];
    }
}
