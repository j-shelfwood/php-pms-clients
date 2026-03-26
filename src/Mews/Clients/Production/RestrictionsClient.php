<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Responses\RestrictionsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Support\RestrictionMinStayResolver;

class RestrictionsClient
{
    public function __construct(
        private readonly MewsHttpClient $httpClient
    ) {}

    /**
     * Get all restrictions for a service across a date range
     *
     * Handles cursor-based pagination with infinite loop protection.
     * The Mews API has a bug where it can return the same cursor indefinitely.
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
        $seenCursors = [];
        $maxPages = 100; // Safety limit to prevent infinite loops
        $pageCount = 0;

        do {
            // Infinite loop protection: detect cursor repetition
            if ($cursor !== null && isset($seenCursors[$cursor])) {
                // API returned same cursor twice - pagination complete
                break;
            }

            // Safety limit: prevent runaway pagination
            if (++$pageCount > $maxPages) {
                throw new MewsApiException(
                    "Restrictions pagination exceeded {$maxPages} pages. " .
                    "This may indicate an API issue or excessive data."
                );
            }

            if ($cursor !== null) {
                $seenCursors[$cursor] = true;
            }

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
            
            // Only merge results if we haven't seen this cursor before
            if ($cursor === null || !isset($seenCursors[$cursor])) {
                $allRestrictions = array_merge($allRestrictions, $pageResponse->items->all());
            }
            
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new RestrictionsResponse(items: collect($allRestrictions));
    }

    /**
     * Find minimum stay requirement for a specific date and resource category.
     *
     * Only Stay-type restrictions govern minimum stay length. Start/End restrictions
     * control check-in/check-out day eligibility and do not affect stay_minimum.
     *
     * Handles open-ended restrictions (null startUtc/endUtc = applies indefinitely).
     * Returns the most restrictive (longest) minimum stay when multiple apply.
     *
     * @param array<Restriction> $restrictions All restrictions data
     * @param Carbon $date Date to check
     * @param string $resourceCategoryId Resource category UUID
     * @return int|null Most restrictive minimum stay in nights, or null if none found
     */
    public function findMinimumStayForDate(array $restrictions, Carbon $date, string $resourceCategoryId): ?int
    {
        return RestrictionMinStayResolver::resolveForDate($restrictions, $date, $resourceCategoryId);
    }
}
