<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Exception;
use Carbon\Carbon;

class CalendarChangesResponse
{
    public function __construct(
        public readonly array $properties,
        public readonly ?int $amount = null,
        public readonly ?array $changes = null
    ) {}

    /**
     * Maps the raw XML response data to a CalendarChangesResponse object.
     *
     * @param  array  $data  The raw response data from the XMLClient.
     */
    public static function map(array $data): self
    {
        try {
            $properties = [];
            foreach ($data['property'] as $property) {
                $properties[] = CalendarChange::fromXml($property);
            }
            return new self(
                properties: $properties,
                amount: count($properties),
                changes: $properties
            );
        } catch (\Throwable $e) {
            throw new \Shelfwood\PhpPms\Exceptions\MappingException($e->getMessage(), 0, $e);
        }
    }
}
