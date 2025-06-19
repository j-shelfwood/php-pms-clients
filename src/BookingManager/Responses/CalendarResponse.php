<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Exception;
use Carbon\Carbon;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarRate;
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
     * Handles both calendar.xml and availability.xml formats.
     *
     * @param  array  $rawResponse  The raw response data from the XMLClient.
     * @param  Carbon|null  $startDate  Start date for availability.xml mapping
     * @param  Carbon|null  $endDate  End date for availability.xml mapping
     *
     * @throws MappingException If mapping fails.
     */
    public static function map(array $rawResponse, ?Carbon $startDate = null, ?Carbon $endDate = null): self
    {
        try {
            // Handle availability.xml response format when startDate/endDate are provided
            if ($startDate && $endDate) {
                return self::mapFromAvailabilityResponse($rawResponse, $startDate, $endDate);
            }

            // Handle calendar.xml response format only
            $calendarData = $rawResponse['calendar'] ?? [];
            if (empty($calendarData) && isset($rawResponse['calendars']['calendar'])) {
                $calendarData = $rawResponse['calendars']['calendar'];
            } elseif (empty($calendarData) && isset($rawResponse['calendars'])) {
                $calendarsArray = $rawResponse['calendars'];
                if (isset($calendarsArray['calendar'][0])) {
                    $calendarData = $calendarsArray['calendar'][0];
                } elseif (isset($calendarsArray['calendar'])) {
                    $calendarData = $calendarsArray['calendar'];
                }
            }

            if (empty($calendarData)) {
                throw new MappingException('Invalid calendar response: Missing calendar data.');
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
            throw new MappingException('Failed to map CalendarResponse: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Map availability.xml response to calendar format
     */
    private static function mapFromAvailabilityResponse(array $sourceData, Carbon $startDate, Carbon $endDate): self
    {
        $unavailableRanges = [];
        $unavailableData = $sourceData['unavailable'] ?? [];

        // Handle single unavailable period
        if (isset($unavailableData['@attributes'])) {
            $unavailableData = [$unavailableData];
        }

        $propertyId = 0;

        // Extract unavailable date ranges with their modification timestamps
        foreach ($unavailableData as $unavailable) {
            if (!isset($unavailable['start']) || !isset($unavailable['end'])) {
                continue;
            }

            // Get property ID from first unavailable item
            if ($propertyId === 0 && isset($unavailable['@attributes']['property_id'])) {
                $propertyId = (int) $unavailable['@attributes']['property_id'];
            }

            $rangeStart = Carbon::createFromFormat('Y-m-d', $unavailable['start'])->startOfDay();
            $rangeEnd = Carbon::createFromFormat('Y-m-d', $unavailable['end'])->endOfDay();

            // Parse modified timestamp from availability data
            $modified = Carbon::now(); // Default fallback
            if (isset($unavailable['modified'])) {
                try {
                    $modified = Carbon::parse($unavailable['modified']);
                } catch (\Exception $e) {
                    // Keep default if parsing fails
                }
            }

            $unavailableRanges[] = [
                'start' => $rangeStart,
                'end' => $rangeEnd,
                'modified' => $modified
            ];
        }

        // Generate calendar days for the requested date range
        $days = [];
        $current = $startDate->copy()->startOfDay();

        while ($current->lte($endDate)) {
            $isAvailable = true;
            $dayModified = Carbon::now(); // Default for available days

            // Check if current date falls within any unavailable range
            foreach ($unavailableRanges as $range) {
                if ($current->between($range['start'], $range['end'])) {
                    $isAvailable = false;
                    $dayModified = $range['modified']; // Use the actual modified timestamp
                    break;
                }
            }

            $dayInfo = new CalendarDayInfo(
                day: $current->copy(),
                season: null,
                modified: $dayModified,
                available: $isAvailable ? 1 : 0,
                stayMinimum: 1, // Default minimum stay
                rate: CalendarRate::fromXml([]), // Rate information not available from availability.xml
                maxStay: null,
                closedOnArrival: !$isAvailable,
                closedOnDeparture: !$isAvailable,
                stopSell: !$isAvailable
            );

            $days[] = $dayInfo;
            $current->addDay();
        }

        return new self($propertyId, $days);
    }
}
