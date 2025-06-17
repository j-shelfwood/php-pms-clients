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

            // Note: This mapping handles both legacy calendar.xml and new approaches

            // Handle availability.xml response format (new approach)
            if (isset($sourceData['availability'])) {
                $availabilityData = $sourceData['availability'];

                // Handle single unavailable period
                if (isset($availabilityData['unavailable']['@attributes'])) {
                    $unavailableItems = [$availabilityData['unavailable']];
                } else {
                    // Handle multiple unavailable periods
                    $unavailableItems = $availabilityData['unavailable'] ?? [];
                    if (!is_array($unavailableItems) || (isset($unavailableItems['@attributes']) && !isset($unavailableItems[0]))) {
                        $unavailableItems = [$unavailableItems];
                    }
                }

                // Get property ID from first unavailable item
                $propertyId = 0;
                if (!empty($unavailableItems) && isset($unavailableItems[0]['@attributes']['property_id'])) {
                    $propertyId = (int) $unavailableItems[0]['@attributes']['property_id'];
                }

                // Convert unavailable periods to calendar days
                $days = array_filter(array_map(function ($unavailable) {
                    try {
                        return CalendarDayInfo::fromAvailabilityXml($unavailable);
                    } catch (Exception $e) {
                        return null;
                    }
                }, $unavailableItems));

                return new self($propertyId, $days);
            }

            // Handle info.xml response format (fallback)
            if (isset($sourceData['info'])) {
                $infoData = $sourceData['info'];
                $propertyId = (int) ($infoData['property']['@attributes']['id'] ?? 0);

                // Extract calendar info from property data in info.xml response
                $propertyData = $infoData['property'] ?? [];
                if (isset($propertyData['@attributes'])) {
                    // Single property response
                    $days = [CalendarDayInfo::fromInfoXml($propertyData)];
                } else {
                    // Multiple properties (shouldn't happen for calendar calls but handle gracefully)
                    $days = array_filter(array_map(function ($prop) {
                        try {
                            return CalendarDayInfo::fromInfoXml($prop);
                        } catch (Exception $e) {
                            return null;
                        }
                    }, is_array($propertyData) ? $propertyData : [$propertyData]));
                }

                return new self($propertyId, $days);
            }

            // Handle legacy calendar.xml response format (test compatibility)
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

            if (!empty($calendarData)) {
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
            }

            // If no recognized format, return empty calendar
            // This is normal for availability.xml when there are no blocked dates
            return new self(0, []);

        } catch (Exception $e) {
            throw new MappingException($e->getMessage(), 0, $e);
        }
    }
}
