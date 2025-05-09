<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Exception;
use Carbon\Carbon;

class CalendarChangesResponse
{
    public function __construct(
        public readonly int $amount,
        public readonly Carbon $time,
        /**
         * @var CalendarChange[]
         */
        public readonly array $changes,
    ) {
    }

    /**
     * Maps the raw XML response data to a CalendarChangesResponse object.
     *
     * @param  array  $rawResponse  The raw response data from the XMLClient.
     */
    public static function map(array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse;

            $changesData = $sourceData['changes'] ?? $sourceData;
            $attributes = $changesData['@attributes'] ?? [];

            $amount = (int) ($attributes['amount'] ?? 0);
            $timeStr = $attributes['time'] ?? null;
            $time = $timeStr ? Carbon::parse($timeStr) : Carbon::now();

            $changeItemsRaw = $changesData['change'] ?? [];
            if (!is_array($changeItemsRaw) || (isset($changeItemsRaw['@attributes']) && !isset($changeItemsRaw[0]))) {
                 $changeItemsRaw = empty($changeItemsRaw) ? [] : [$changeItemsRaw];
            }

            $changes = array_map(
                static function (array $calendarChangeXML) {
                    return CalendarChange::fromXml($calendarChangeXML);
                },
                $changeItemsRaw
            );

            return new self($amount, $time, $changes);

        } catch (Exception $e) {
            throw new Exception('Failed to map CalendarChangesResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
