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

        // Check if we have structured content (short, full, etc.) or language-based content (en, nl, etc.)
        $hasStructuredContent = isset($data['short']) || isset($data['full']) || isset($data['area']) || isset($data['arrival']) || isset($data['tac']);

        if ($hasStructuredContent) {
            // Use structured content format
            return new self(
                short: $getText('short'),
                full: $getText('full'),
                area: $getText('area'),
                arrival: $getText('arrival'),
                termsAndConditions: $getText('tac')
            );
        } else {
            // Use language-based content format - map first available language to short description
            $languageText = '';
            if (isset($data['en'])) {
                $languageText = (string)$data['en'];
            } elseif (isset($data['nl'])) {
                $languageText = (string)$data['nl'];
            } else {
                // Take first available language
                foreach ($data as $key => $value) {
                    if (is_string($value) && !empty($value)) {
                        $languageText = $value;
                        break;
                    }
                }
            }

            return new self(
                short: $languageText,
                full: '',
                area: '',
                arrival: '',
                termsAndConditions: ''
            );
        }
    }
}
