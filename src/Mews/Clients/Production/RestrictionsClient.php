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
     * Maximum days per request allowed by the Mews API.
     * Requests exceeding this will return a 400 Bad Request error.
     */
    private const MAX_RANGE_DAYS = 90;

    /**
     * Default page size for restrictions/getAll. Mews returns at most this many
     * items per cursor page; consumers detect the final page when items < PAGE_SIZE.
     */
    public const PAGE_SIZE = 1000;

    /**
     * Get all restrictions for a service across a date range.
     *
     * Automatically chunks requests into ≤90-day windows when the range exceeds
     * the Mews API limit of 100 days (we use 90 for safety margin).
     * Handles cursor-based pagination with infinite loop protection per chunk.
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
        $chunkStart = $start->copy();

        while ($chunkStart->lte($end)) {
            $chunkEnd = $chunkStart->copy()->addDays(self::MAX_RANGE_DAYS - 1);
            if ($chunkEnd->gt($end)) {
                $chunkEnd = $end->copy();
            }

            $chunkItems = $this->fetchChunk($serviceId, $chunkStart, $chunkEnd, $resourceCategoryIds);
            $allRestrictions = array_merge($allRestrictions, $chunkItems);

            $chunkStart->addDays(self::MAX_RANGE_DAYS);
        }

        return new RestrictionsResponse(items: collect($allRestrictions));
    }

    /**
     * Fetch all pages of restrictions for a single ≤90-day chunk.
     *
     * @return array<Restriction>
     * @throws MewsApiException
     */
    private function fetchChunk(
        string $serviceId,
        Carbon $start,
        Carbon $end,
        ?array $resourceCategoryIds
    ): array {
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

            $pageResponse = $this->getPage(
                $serviceId,
                $start,
                $end,
                $cursor,
                self::PAGE_SIZE,
                $resourceCategoryIds
            );

            // Only merge results if we haven't seen this cursor before
            if ($cursor === null || !isset($seenCursors[$cursor])) {
                $allRestrictions = array_merge($allRestrictions, $pageResponse->items->all());
            }

            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return $allRestrictions;
    }

    /**
     * Fetch a single page of restrictions for cursor-based pagination.
     *
     * Use this when you need fine-grained control over pagination — e.g. when each
     * page becomes its own queued job rather than being collected in-process.
     * For most callers prefer `getAll()`, which handles chunking + pagination internally.
     *
     * The caller is responsible for:
     * - Keeping requests within the 100-day API limit (use windows ≤ 90 days for safety)
     * - Detecting the last page (items < PAGE_SIZE), guarding against the Mews API bug
     *   where the same cursor is returned indefinitely for single-page result sets
     * - Driving the loop / dispatching follow-up pages
     *
     * @param string $serviceId Service UUID
     * @param Carbon $start Start date (UTC) — must be within 90 days of $end
     * @param Carbon $end End date (UTC)
     * @param string|null $cursor Cursor from previous page (null for first page)
     * @param int $pageSize Items per page (default: PAGE_SIZE)
     * @param array<int, string>|null $resourceCategoryIds Specific categories (optional)
     * @return RestrictionsResponse Single page with cursor for next page (or null if last)
     * @throws MewsApiException
     */
    public function getPage(
        string $serviceId,
        Carbon $start,
        Carbon $end,
        ?string $cursor = null,
        int $pageSize = self::PAGE_SIZE,
        ?array $resourceCategoryIds = null
    ): RestrictionsResponse {
        $body = $this->httpClient->buildRequestBody([
            'ServiceIds' => [$serviceId],
            'CollidingUtc' => [
                'StartUtc' => $start->toIso8601String(),
                'EndUtc' => $end->toIso8601String(),
            ],
            'ResourceCategoryIds' => $resourceCategoryIds,
            'Limitation' => ['Count' => $pageSize],
            'Cursor' => $cursor,
        ]);

        $response = $this->httpClient->post('/api/connector/v1/restrictions/getAll', $body);

        return RestrictionsResponse::map($response);
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
