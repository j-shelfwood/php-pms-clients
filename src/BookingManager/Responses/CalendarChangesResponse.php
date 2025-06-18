<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Exception;
use Carbon\Carbon;

class CalendarChangesResponse
{
    public function __construct(
        public readonly array $changes,
        public readonly ?int $amount = null,
        public readonly ?Carbon $time = null
    ) {}

    /**
     * Maps the raw XML response data to a CalendarChangesResponse object.
     *
     * @param  array  $data  The raw response data from the XMLClient.
     */
    public static function map(array $data): self
    {
        try {
            $changes = [];
            $amount = null;
            $time = null;

            // Extract root attributes
            if (isset($data['@attributes'])) {
                $amount = isset($data['@attributes']['amount']) ? (int)$data['@attributes']['amount'] : null;
                $time = isset($data['@attributes']['time']) ? Carbon::parse($data['@attributes']['time']) : null;
            }

            // Handle change elements
            if (isset($data['change'])) {
                $changeData = $data['change'];

                // Handle single change vs multiple changes
                if (isset($changeData['@attributes'])) {
                    // Single change
                    $changes[] = CalendarChange::fromXml($changeData);
                } else {
                    // Multiple changes
                    foreach ($changeData as $change) {
                        $changes[] = CalendarChange::fromXml($change);
                    }
                }
            }

            return new self(
                changes: $changes,
                amount: $amount ?? count($changes),
                time: $time
            );
        } catch (\Throwable $e) {
            throw new \Shelfwood\PhpPms\Exceptions\MappingException($e->getMessage(), 0, $e);
        }
    }
}
