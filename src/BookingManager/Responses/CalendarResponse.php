<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Exception;
use Tightenco\Collect\Support\Collection;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\CalendarRate;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\CalendarTax;

class CalendarResponse
{
    /** @var Collection<int, CalendarDayInfo> */
    public readonly Collection $days;

    public function __construct(
        public readonly int $propertyId,
        Collection $days
    ) {
        $this->days = $days;
    }

    /**
     * Maps the raw XML response data to a CalendarResponse object.
     *
     * @param  Collection  $rawResponse  The raw response data from the XMLClient.
     *
     * @throws Exception If mapping fails.
     */
    public static function map(Collection|array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse instanceof Collection ? $rawResponse : new Collection($rawResponse);

            $calendarData = new Collection($sourceData->get('calendar'));
            if ($calendarData->isEmpty() && $sourceData->has('calendars.calendar')) {
                $calendarData = new Collection($sourceData->get('calendars.calendar'));
            } elseif ($calendarData->isEmpty() && $sourceData->has('calendars') && is_array($sourceData->get('calendars'))) {
                $calendarsArray = $sourceData->get('calendars');
                if (isset($calendarsArray['calendar'][0])) {
                    $calendarData = new Collection($calendarsArray['calendar'][0]);
                } elseif (isset($calendarsArray['calendar'])) {
                    $calendarData = new Collection($calendarsArray['calendar']);
                }
            }

            if ($calendarData->isEmpty()) {
                // Removed Log::error
                // Consider throwing an exception or returning a default/empty state based on application needs
                // For now, let's throw an exception as missing data is usually critical.
                throw new Exception('Invalid response structure: Missing calendar data.');
                // return new self(0, new Collection()); // Alternative: Return empty if data missing
            }

            $propertyId = (int) ($calendarData->get('@attributes.property_id') ?? $calendarData->get('@attributes.id') ?? 0);

            $infoItems = $calendarData->get('info', []);
            // Ensure $infoItems is always an array of items for consistent processing
            // If $infoItems is a Collection, convert it to an array
            if ($infoItems instanceof Collection) {
                $infoItems = $infoItems->all();
            }

            // If it's not an array or looks like a single associative array item (e.g. has '@attributes')
            if (!is_array($infoItems) || (isset($infoItems['@attributes']) && count($infoItems) <= 2 && !isset($infoItems[0]))) {
                $infoItems = [$infoItems];
            } elseif (empty($infoItems)) {
                $infoItems = [];
            }

            $days = collect($infoItems)
                ->filter(fn ($info) => !empty($info))
                ->map(function ($info) {
                    try {
                        return CalendarDayInfo::fromXml(new Collection($info));
                    } catch (Exception $e) {
                        // Removed Log::error, consider re-throwing or custom error handling
                        // For now, let's allow it to bubble up or return null if that's preferred.
                        // Depending on strictness, you might want to throw new MappingException(...) here.
                        // error_log('Failed to map CalendarDayInfo: ' . $e->getMessage()); // Simple alternative to Log
                        return null;
                    }
                })
                ->filter(); // Remove nulls from failed mappings

            return new self($propertyId, $days);

        } catch (Exception $e) {
            // Removed Log::error
            // error_log('Error parsing calendar response: ' . $e->getMessage()); // Simple alternative to Log
            throw new Exception('Failed to map CalendarResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
