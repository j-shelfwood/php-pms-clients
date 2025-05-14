<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Shelfwood\PhpPms\Exceptions\MappingException;


class PropertyResponse
{
    public function __construct(
        public readonly PropertyDetails $property
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                property: PropertyDetails::map($data)
            );
        } catch (\Throwable $e) {
            throw new MappingException($e->getMessage(), 0, $e);
        }
    }
}
