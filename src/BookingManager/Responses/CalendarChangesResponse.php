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
     * @param  array  $data  The raw response data from the XMLClient.
     */
    public static function map(array $data): self
    {
        try {
            $amount = null;
            $time = null;

            // Extract root attributes
            if (isset($data['@attributes'])) {
                $amount = isset($data['@attributes']['amount']) ? (int)$data['@attributes']['amount'] : null;
                $time = isset($data['@attributes']['time']) ? Carbon::parse($data['@attributes']['time']) : null;
            }

            $changes = collect();

            // Handle change elements
            if (isset($data['change'])) {
                $changeData = $data['change'];

                // Handle single change vs multiple changes
                if (isset($changeData['@attributes'])) {
                    // Single change
                    $changes->push(CalendarChange::fromXml($changeData));
                } else {
                    // Multiple changes
                    $changes = collect($changeData)
                        ->map(fn($change) => CalendarChange::fromXml($change));
                }
            }

            return new self(
                changes: $changes,
                amount: $amount ?? $changes->count(),
                time: $time
            );
        } catch (\Throwable $e) {
            throw new \Shelfwood\PhpPms\Exceptions\MappingException($e->getMessage(), 0, $e);
        }
    }
}
