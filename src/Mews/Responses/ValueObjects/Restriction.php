<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class Restriction
{
    /**
     * @param string $id
     * @param string $serviceId
     * @param string|null $externalIdentifier
     * @param string $origin
     * @param RestrictionConditions $conditions
     * @param RestrictionExceptions $exceptions
     * @param string $createdUtc
     * @param string $updatedUtc
     */
    public function __construct(
        public readonly string $id,
        public readonly string $serviceId,
        public readonly ?string $externalIdentifier,
        public readonly string $origin,
        public readonly RestrictionConditions $conditions,
        public readonly RestrictionExceptions $exceptions,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                serviceId: $data['ServiceId'] ?? throw new \InvalidArgumentException('ServiceId required'),
                externalIdentifier: $data['ExternalIdentifier'] ?? null,
                origin: $data['Origin'] ?? 'User',
                conditions: isset($data['Conditions'])
                    ? RestrictionConditions::map($data['Conditions'])
                    : throw new \InvalidArgumentException('Conditions is required'),
                exceptions: isset($data['Exceptions'])
                    ? RestrictionExceptions::map($data['Exceptions'])
                    : throw new \InvalidArgumentException('Exceptions is required'),
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Restriction: {$e->getMessage()}", 0, $e);
        }
    }
}
