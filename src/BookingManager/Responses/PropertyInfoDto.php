<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

/**
 * Read-only DTO representing property info for mapping purposes.
 */
class PropertyInfoDto
{
    public function __construct(
        public readonly int $externalId,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $type = null,
        public readonly ?int $providerId = null,
        public readonly ?string $street = null,
        public readonly ?string $zipcode = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null
    ) {}
}
