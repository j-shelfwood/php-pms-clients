<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Exception;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Shelfwood\PhpPms\Exceptions\MappingException;

class CalendarResponse
{
    /** @var array<int, CalendarDayInfo> */
    public readonly array $days;

    public function __construct(
        public readonly int $propertyId,
        array $days
    ) {
        $this->days = $days;
    }

    /**
     * Maps the raw XML response data to a CalendarResponse object.
     *
     * @param  array  $rawResponse  The raw response data from the XMLClient.
     *
     * @throws Exception If mapping fails.
     */
    public static function map(array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse;

            $calendarData = $sourceData['calendar'] ?? [];
            if (empty($calendarData) && isset($sourceData['calendars']['calendar'])) {
                $calendarData = $sourceData['calendars']['calendar'];
            } elseif (empty($calendarData) && isset($sourceData['calendars'])) {
                $calendarsArray = $sourceData['calendars'];
                if (isset($calendarsArray['calendar'][0])) {
                    $calendarData = $calendarsArray['calendar'][0];
                } elseif (isset($calendarsArray['calendar'])) {
                    $calendarData = $calendarsArray['calendar'];
                }
            }

            if (empty($calendarData)) {
                throw new Exception('Invalid response structure: Missing calendar data.');
            }

            $propertyId = (int) ($calendarData['@attributes']['property_id'] ?? $calendarData['@attributes']['id'] ?? 0);

            $infoItems = $calendarData['info'] ?? [];
            if (!is_array($infoItems) || (isset($infoItems['@attributes']) && !isset($infoItems[0]))) {
                $infoItems = [$infoItems];
            }

            $days = array_filter(array_map(function ($info) {
                try {
                    return CalendarDayInfo::fromXml($info);
                } catch (Exception $e) {
                    return null;
                }
            }, $infoItems));

            return new self($propertyId, $days);

        } catch (Exception $e) {
            throw new MappingException($e->getMessage(), 0, $e);
        }
    }
}
