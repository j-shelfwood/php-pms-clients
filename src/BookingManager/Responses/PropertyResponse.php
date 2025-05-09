<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;


class PropertyResponse
{
    public function __construct(
        public readonly PropertyDetails $property
    ) {}

    public static function map(array $data): self
    {
        return new self(
            property: PropertyDetails::map($data)
        );
    }
}
