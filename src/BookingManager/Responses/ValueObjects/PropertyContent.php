<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



class PropertyContent
{
    public function __construct(
        public readonly string $short,
        public readonly string $full,
        public readonly string $area,
        public readonly string $arrival,
        public readonly string $termsAndConditions
    ) {}

    public static function fromXml(array $data): self
    {
        $getText = function ($key) use ($data) {
            $value = $data[$key] ?? null;
            if (is_array($value)) {
                return (string)($value['#text'] ?? '');
            }
            return (string)($value ?? '');
        };
        return new self(
            short: $getText('short'),
            full: $getText('full'),
            area: $getText('area'),
            arrival: $getText('arrival'),
            termsAndConditions: $getText('tac')
        );
    }
}
