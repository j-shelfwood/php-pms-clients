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
                // Handle locale-based content like: <short locale="en_gb">...</short>
                if (isset($value['#text'])) {
                    return (string)$value['#text'];
                }
                // Handle simple language codes like: <en>...</en>, <nl>...</nl>
                // Prefer English if available, otherwise take first available
                if (isset($value['en'])) {
                    return (string)$value['en'];
                }
                if (isset($value['nl'])) {
                    return (string)$value['nl'];
                }
                // Take first available value
                $firstValue = reset($value);
                return is_string($firstValue) ? $firstValue : '';
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
