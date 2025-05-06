<?php

namespace Domain\Connections\BookingManager\Responses;

use Exception;
use Illuminate\Support\Collection; // Added for logging
use Illuminate\Support\Facades\Log; // Added for exception handling
use Domain\Connections\BookingManager\Responses\ValueObjects\CalendarDayInfo;

// Add use statements for the extracted classes
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
            // The structure might be nested under 'calendars' -> 'calendar'
            $calendarData = collect($rawResponse->get('calendar'));
            if ($calendarData->isEmpty() && $rawResponse->has('calendars.calendar')) {
                // Handle cases where it's nested like <calendars><calendar>...</calendar></calendars>
                $calendarData = collect($rawResponse->get('calendars.calendar'));
            } elseif ($calendarData->isEmpty() && $rawResponse->has('calendars') && is_array($rawResponse->get('calendars'))) {
                // Handle cases like <calendars><calendar>...</calendar><calendar>...</calendar></calendars> - This shouldn't happen for single property calendar but check anyway
                // This case is less likely for a single property calendar response, usually it's one <calendar> tag.
                // If multiple calendars were possible, logic to handle an array here would be needed.
                Log::warning('CalendarResponse::map encountered unexpected structure with multiple calendars.', ['response' => $rawResponse]);
                // For now, assume the first one if multiple exist, though API docs suggest one.
                $calendarsArray = $rawResponse->get('calendars');
                if (isset($calendarsArray['calendar'][0])) {
                    $calendarData = collect($calendarsArray['calendar'][0]);
                } elseif (isset($calendarsArray['calendar'])) {
                    $calendarData = collect($calendarsArray['calendar']);
                }
            }

            if ($calendarData->isEmpty()) {
                Log::error('CalendarResponse::map - Calendar data is empty or not found in expected structure.', ['response' => $rawResponse]);

                // Depending on requirements, either throw an exception or return an empty response
                // throw new Exception('Invalid response structure: Missing calendar data.');
                return new self(0, collect()); // Return empty if data missing
            }

            $propertyId = (int) ($calendarData->get('@attributes.property_id') ?? $calendarData->get('@attributes.id') ?? 0); // Check both property_id and id

            // The 'info' key might hold a single item or an array of items if multiple days are returned.
            $infoItems = $calendarData->get('info', []);

            // Ensure $infoItems is always an array of items for consistent processing
            if (! is_array($infoItems) || (isset($infoItems['@attributes']) && count($infoItems) <= 2)) { // Basic check if it looks like a single item assoc array
                // Handle case where only one 'info' item exists (not an indexed array)
                $infoItems = [$infoItems];
            } elseif (empty($infoItems)) {
                $infoItems = []; // Ensure it's an empty array if no info items found
            }

            $days = collect($infoItems)
                ->filter(fn ($info) => ! empty($info)) // Filter out potentially empty entries if XML was weird
                ->map(function ($info) {
                    try {
                        // Ensure $info is a collection for consistent processing
                        return CalendarDayInfo::fromXml(collect($info));
                    } catch (Exception $e) {
                        Log::error('Failed to map CalendarDayInfo from XML fragment', [
                            'error' => $e->getMessage(),
                            'xml_fragment' => $info,
                        ]);

                        return null; // Return null for failed mappings
                    }
                })
                ->filter(); // Remove nulls from failed mappings

            return new self($propertyId, $days);

        } catch (Exception $e) {
            Log::error('Error parsing calendar response', ['error' => $e->getMessage(), 'response' => $rawResponse]);
            // Re-throw or handle as appropriate for your application flow
            throw new Exception('Failed to map CalendarResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
