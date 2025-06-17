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
     *
     * @param  array  $rawResponse  The raw response data from the XMLClient.
     *
     * @throws Exception If mapping fails.
     */
    public static function map(array $rawResponse, Carbon $startDate = null, Carbon $endDate = null): self
    {
        try {
            $sourceData = $rawResponse;

            // Handle availability.xml response format (direct unavailable elements)
            // Note: availability.xml may have no unavailable elements if everything is available
            if ($startDate && $endDate && (isset($sourceData['unavailable']) || empty($sourceData) || (count($sourceData) === 0))) {
                return self::mapFromAvailabilityResponse($sourceData, $startDate, $endDate);
            }

            // Handle rate.xml response format (from info.xml endpoint)
            if (isset($sourceData['rate'])) {
                $rateData = $sourceData['rate'];
                $infoData = $rateData['info'] ?? [];

                // Extract property information
                $propertyData = $infoData['property'] ?? [];
                $propertyId = (int) ($propertyData['@attributes']['id'] ?? 0);

                // For info.xml response, we get availability info for a range
                // We need to create calendar days for the range
                $arrival = isset($infoData['@attributes']['arrival']) ?
                    Carbon::parse($infoData['@attributes']['arrival']) : Carbon::now();
                $departure = isset($infoData['@attributes']['departure']) ?
                    Carbon::parse($infoData['@attributes']['departure']) : Carbon::now()->addDay();

                $isAvailable = (bool)($propertyData['@attributes']['available'] ?? false);
                $minimalNights = (int)($propertyData['@attributes']['minimal_nights'] ?? 1);

                $days = [];
                for ($date = $arrival->copy(); $date->lt($departure); $date->addDay()) {
                    $days[] = new CalendarDayInfo(
                        day: $date->copy(),
                        season: null,
                        modified: Carbon::now(),
                        available: $isAvailable ? 1 : 0,
                        stayMinimum: $minimalNights,
                        rate: CalendarRate::fromXml($propertyData['rate'] ?? []),
                        maxStay: (int)($propertyData['@attributes']['max_persons'] ?? null),
                        closedOnArrival: !$isAvailable,
                        closedOnDeparture: !$isAvailable,
                        stopSell: !$isAvailable
                    );
                }

                return new self($propertyId, $days);
            }

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

        // Extract unavailable date ranges
        foreach ($unavailableData as $unavailable) {
            if (!isset($unavailable['start']) || !isset($unavailable['end'])) {
                continue;
            }

            // Get property ID from first unavailable item
            if ($propertyId === 0 && isset($unavailable['@attributes']['property_id'])) {
                $propertyId = (int) $unavailable['@attributes']['property_id'];
            }

            $rangeStart = Carbon::createFromFormat('Y-m-d', $unavailable['start']);
            $rangeEnd = Carbon::createFromFormat('Y-m-d', $unavailable['end']);

            $unavailableRanges[] = [
                'start' => $rangeStart,
                'end' => $rangeEnd
            ];
        }

        // Generate calendar days for the requested date range
        $days = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $isAvailable = true;

            // Check if current date falls within any unavailable range
            foreach ($unavailableRanges as $range) {
                if ($current->between($range['start'], $range['end'])) {
                    $isAvailable = false;
                    break;
                }
            }

            $dayInfo = new CalendarDayInfo(
                day: $current->copy(),
                season: null,
                modified: Carbon::now(),
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
