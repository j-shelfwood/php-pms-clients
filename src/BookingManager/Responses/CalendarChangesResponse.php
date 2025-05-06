<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\CalendarChange;
use Exception;
use Carbon\Carbon; // Keep Carbon
use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class CalendarChangesResponse
{
    public function __construct(
        public readonly int $amount,
        public readonly Carbon $time,
        /** @var Collection<int, CalendarChange> */
        public readonly Collection $changes,
    ) {
        //
    }

    /**
     * Maps the raw XML response data to a CalendarChangesResponse object.
     *
     * @param  Collection  $rawResponse  The raw response data from the XMLClient.
     */
    public static function map(Collection|array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse instanceof Collection ? $rawResponse : new Collection($rawResponse);

            // Data is expected under the root 'changes' element or might be the root itself
            $changesData = new Collection($sourceData->get('changes') ?? $sourceData);
            $attributes = new Collection($changesData->get('@attributes', []));

            $amount = (int) $attributes->get('amount', 0);
            $timeStr = $attributes->get('time');
            $time = $timeStr ? Carbon::parse($timeStr) : Carbon::now();

            $changeItemsRaw = $changesData->get('change', []);
            // Ensure it's always an array of items for consistent processing
            if ($changeItemsRaw instanceof Collection) {
                $changeItemsRaw = $changeItemsRaw->all();
            }
            if (!is_array($changeItemsRaw) || (isset($changeItemsRaw['@attributes']) && !isset($changeItemsRaw[0]))) {
                 $changeItemsRaw = empty($changeItemsRaw) ? [] : [$changeItemsRaw];
            }

            $changes = collect($changeItemsRaw)
                ->map(fn ($item) => CalendarChange::fromXml(new Collection($item)))
                ->filter(); // Remove potential nulls if mapping fails

            return new self($amount, $time, $changes);

        } catch (Exception $e) {
            throw new Exception('Failed to map CalendarChangesResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
