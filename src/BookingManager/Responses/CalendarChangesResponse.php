<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Exception;
use Carbon\Carbon;

class CalendarChangesResponse
{
    /**
     * @param Collection<int, CalendarChange> $changes
     * @param int|null $amount
     * @param Carbon|null $time
     */
    public function __construct(
        public readonly Collection $changes,
        public readonly ?int $amount = null,
        public readonly ?Carbon $time = null
    ) {}

    /**
     * Maps the raw XML response data to a CalendarChangesResponse object.
     *
     * Parses BookingManager changes.xml endpoint response format:
     * - Root <changes> element with no attributes
     * - Child <change> elements with:
     *   - type: Change category (details, availability, rate)
     *   - ids: Comma-separated list of property IDs
     *   - time: Timestamp when change occurred
     *   - amount: Number of properties in this change
     *
     * Returns unique property IDs across all change types with latest timestamp.
     *
     * @param  array  $data  The raw response data from the XMLClient.
     * @return self
     * @throws \Shelfwood\PhpPms\Exceptions\MappingException
     */
    public static function map(array $data): self
    {
        try {
            $allPropertyIds = collect();
            $latestTime = null;

            // Handle empty response
            if (!isset($data['change'])) {
                return new self(
                    changes: collect(),
                    amount: 0,
                    time: null
                );
            }

            $changeData = $data['change'];

            // Normalize to array of changes (handle single vs multiple)
            if (isset($changeData['@attributes'])) {
                // Single change element - wrap in array
                $changeData = [$changeData];
            }

            // Process each change type (details, availability, rate)
            foreach ($changeData as $change) {
                $attrs = $change['@attributes'] ?? [];

                // Extract property IDs from comma-separated list
                if (isset($attrs['ids']) && !empty(trim($attrs['ids']))) {
                    $ids = array_map('trim', explode(',', $attrs['ids']));
                    $ids = array_filter($ids, fn($id) => !empty($id));
                    $allPropertyIds = $allPropertyIds->merge($ids);
                }

                // Track latest change time across all change types
                if (isset($attrs['time'])) {
                    $changeTime = Carbon::parse($attrs['time']);
                    if ($latestTime === null || $changeTime->gt($latestTime)) {
                        $latestTime = $changeTime;
                    }
                }
            }

            // Create CalendarChange objects for unique property IDs
            $changes = $allPropertyIds
                ->unique()
                ->sort()
                ->values()
                ->map(fn($id) => new CalendarChange(
                    propertyId: (int)$id,
                    months: [] // BookingManager API doesn't provide month-level granularity
                ));

            return new self(
                changes: $changes,
                amount: $changes->count(),
                time: $latestTime
            );
        } catch (\Throwable $e) {
            throw new \Shelfwood\PhpPms\Exceptions\MappingException(
                'Failed to map CalendarChangesResponse: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
