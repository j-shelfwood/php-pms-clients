<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class RestrictionExceptions
{
    /**
     * @param string|null $minAdvance ISO 8601 duration (e.g. "P0M3DT0H0M0S")
     * @param string|null $maxAdvance ISO 8601 duration (e.g. "P0M3DT0H0M0S")
     * @param string|null $minLength ISO 8601 duration representing minimum stay length
     * @param string|null $maxLength ISO 8601 duration representing maximum stay length
     * @param array<string, mixed>|null $minPrice Amount object with currency and value
     * @param array<string, mixed>|null $maxPrice Amount object with currency and value
     * @param int|null $minReservationCount
     * @param int|null $maxReservationCount
     */
    public function __construct(
        public readonly ?string $minAdvance,
        public readonly ?string $maxAdvance,
        public readonly ?string $minLength,
        public readonly ?string $maxLength,
        public readonly ?array $minPrice,
        public readonly ?array $maxPrice,
        public readonly ?int $minReservationCount,
        public readonly ?int $maxReservationCount,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                minAdvance: $data['MinAdvance'] ?? null,
                maxAdvance: $data['MaxAdvance'] ?? null,
                minLength: $data['MinLength'] ?? null,
                maxLength: $data['MaxLength'] ?? null,
                minPrice: $data['MinPrice'] ?? null,
                maxPrice: $data['MaxPrice'] ?? null,
                minReservationCount: $data['MinReservationCount'] ?? null,
                maxReservationCount: $data['MaxReservationCount'] ?? null,
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map RestrictionExceptions: {$e->getMessage()}", 0, $e);
        }
    }
}
