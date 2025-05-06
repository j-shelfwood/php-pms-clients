<?php

namespace Domain\Connections\Cubilis\Dtos;

class RatePlanDto
{
    public function __construct(
        public readonly string $ratePlanId,
        public readonly string $name
    ) {}

    public static function fromArray(array $data): self
    {
        $attrs = $data['@attributes'] ?? [];
        return new self(
            ratePlanId: (string) ($attrs['RatePlanID'] ?? ''),
            name: (string) ($attrs['RatePlanName'] ?? '')
        );
    }
}
