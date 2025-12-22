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
            $allRestrictions = array_merge($allRestrictions, $pageResponse->items);
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new RestrictionsResponse(items: $allRestrictions);
    }

    /**
     * Find minimum stay requirement for a specific date and resource category
     *
     * @param array<Restriction> $restrictions All restrictions data
     * @param Carbon $date Date to check
     * @param string $resourceCategoryId Resource category UUID
     * @return int|null Maximum applicable minimum stay, or null if none found
     */
    public function findMinimumStayForDate(array $restrictions, Carbon $date, string $resourceCategoryId): ?int
    {
        $applicableStays = [];

        foreach ($restrictions as $restriction) {
            if ($restriction->resourceCategoryId !== $resourceCategoryId) {
                continue;
            }

            $start = Carbon::parse($restriction->startUtc);
            $end = Carbon::parse($restriction->endUtc);

            if ($date->between($start, $end)) {
                if ($restriction->minimumStay !== null) {
                    $applicableStays[] = $restriction->minimumStay;
                }
            }
        }

        return !empty($applicableStays) ? max($applicableStays) : null;
    }
}
