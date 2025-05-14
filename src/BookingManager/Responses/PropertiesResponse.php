<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;

class PropertiesResponse
{
    public function __construct(
        /** @var PropertyDetails[] */
        public readonly array $properties
    ) {}
    public static function map(array $data): self
    {
        try {
            $properties = [];
            foreach ($data['property'] as $property) {
                $properties[] = PropertyDetails::map($property);
            }
            return new self(
                properties: $properties
            );
        } catch (\Throwable $e) {
            throw new \Shelfwood\PhpPms\Exceptions\MappingException($e->getMessage(), 0, $e);
        }
    }
}