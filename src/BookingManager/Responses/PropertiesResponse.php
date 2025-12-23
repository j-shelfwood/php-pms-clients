<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;

class PropertiesResponse
{
    /**
     * @param Collection<int, PropertyDetails> $properties
     */
    public function __construct(
        public readonly Collection $properties
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                properties: collect($data['property'] ?? [])
                    ->map(fn($property) => PropertyDetails::map($property))
            );
        } catch (\Throwable $e) {
            throw new \Shelfwood\PhpPms\Exceptions\MappingException($e->getMessage(), 0, $e);
        }
    }
}