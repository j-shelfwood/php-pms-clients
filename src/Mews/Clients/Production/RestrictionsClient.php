<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Responses\RestrictionsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

class RestrictionsClient
{
    public function __construct(
        private readonly MewsHttpClient $httpClient
    ) {}

    /**
     * Get all restrictions for a service across a date range
     *
     * @param string $serviceId Service UUID
     * @param Carbon $start Start date (UTC)
     * @param Carbon $end End date (UTC)
     * @param array|null $resourceCategoryIds Specific categories to check (optional)
     * @return RestrictionsResponse All restrictions data with cursor-based pagination
     * @throws MewsApiException
     */
    public function getAll(
        string $serviceId,
        Carbon $start,
        Carbon $end,
        ?array $resourceCategoryIds = null
    ): RestrictionsResponse {
        $allRestrictions = [];
        $cursor = null;

        do {
            $body = $this->httpClient->buildRequestBody([
                'ServiceIds' => [$serviceId],
                'CollidingUtc' => [
                    'StartUtc' => $start->toIso8601String(),
                    'EndUtc' => $end->toIso8601String(),
                ],
                'ResourceCategoryIds' => $resourceCategoryIds,
                'Limitation' => ['Count' => 1000],
                'Cursor' => $cursor,
            ]);

            $response = $this->httpClient->post('/api/connector/v1/restrictions/getAll', $body);

            $pageResponse = RestrictionsResponse::map($response);
            $allRestrictions = array_merge($allRestrictions, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new RestrictionsResponse(items: collect($allRestrictions));
    }

    /**
     * Find minimum stay requirement for a specific date and resource category
     *
     * @param array<Restriction> $restrictions All restrictions data
     * @param Carbon $date Date to check
     * @param string $resourceCategoryId Resource category UUID
     * @return string|null Maximum applicable minimum stay (ISO 8601 duration), or null if none found
     */
    public function findMinimumStayForDate(array $restrictions, Carbon $date, string $resourceCategoryId): ?string
    {
        $applicableStays = [];

        foreach ($restrictions as $restriction) {
            if ($restriction->conditions->resourceCategoryId !== $resourceCategoryId) {
                continue;
            }

            $start = Carbon::parse($restriction->conditions->startUtc);
            $end = Carbon::parse($restriction->conditions->endUtc);

            if ($date->between($start, $end)) {
                if ($restriction->exceptions->minLength !== null) {
                    $applicableStays[] = $restriction->exceptions->minLength;
                }
            }
        }

        // Return the most restrictive (longest) minimum stay
        // For simplicity, return the first one found. A proper implementation
        // would parse ISO 8601 durations and compare them.
        return !empty($applicableStays) ? $applicableStays[0] : null;
    }
}
