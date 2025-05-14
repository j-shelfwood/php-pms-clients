<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class CalendarChange
{
    public function __construct(
        public readonly int $propertyId,
        /** @var string[] */
        public readonly array $months
    ) {}

    public static function fromXml(array $data): self
    {
        try {
            $attributes = isset($data['@attributes']) ? $data['@attributes'] : [];
            $monthsString = isset($attributes['months']) ? (string)$attributes['months'] : '';
            $months = !empty($monthsString) ? array_map('trim', explode(',', $monthsString)) : [];
            return new self(
                propertyId: isset($attributes['id']) ? (int)$attributes['id'] : 0,
                months: $months
            );
        } catch (\Throwable $e) {
            throw new MappingException('Failed to map CalendarChange: ' . $e->getMessage(), 0, $e);
        }
    }
}
