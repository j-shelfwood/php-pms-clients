<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class TaxValue
{
    public function __construct(
        public readonly ?string $code,
        public readonly float $value,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                code: $data['Code'] ?? null,
                value: (float) ($data['Value'] ?? 0.0),
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map TaxValue: {$e->getMessage()}", 0, $e);
        }
    }
}
