<?php

namespace Domain\Connections\BookingManager\Responses;

use Domain\Connections\BookingManager\Responses\ValueObjects\CalendarChange;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
            // Data is expected under the root 'changes' element
            $changesData = $rawResponse->get('changes') ?? $rawResponse; // Handle if 'changes' is the root
            $attributes = $changesData->get('@attributes', []);

            $amount = (int) Arr::get($attributes, 'amount', 0);
            $timeStr = Arr::get($attributes, 'time');
            $time = $timeStr ? Carbon::parse($timeStr) : Carbon::now(); // Default to now if time is missing

            $changeItemsRaw = $changesData->get('change', []);
            // Ensure it's always an array for consistent processing
            if (! is_array($changeItemsRaw) || (Arr::isAssoc($changeItemsRaw) && ! empty($changeItemsRaw))) {
                $changeItemsRaw = [$changeItemsRaw];
            }

            $changes = collect($changeItemsRaw)
                ->map(fn ($item) => CalendarChange::fromXml(collect($item)))
                ->filter(); // Remove potential nulls if mapping fails

            return new self($amount, $time, $changes);

        } catch (Exception $e) {
            throw new Exception('Failed to map CalendarChangesResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
